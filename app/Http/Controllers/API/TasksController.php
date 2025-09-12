<?php

namespace App\Http\Controllers\API;

use App\Exceptions\OptimisticLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TasksController extends Controller
{

    public function list(Request $request) {
        try {
            $tasks = Task::query()->with(['tags', 'assignedUser']);
            if ($request->user()->role === "user") {
                $tasks->where('assigned_to', Auth::id());
            }

            if ($request->filled('search')) {
                $search = $request->get('search');
                $tasks->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            if ($request->filled('status')) {
                $tasks->where('status', $request->get('status'));
            }

            if ($request->filled('priority')) {
                $tasks->where('priority', $request->get('priority'));
            }

            if ($request->filled('assigned_to')) {
                $tasks->where('assigned_to', $request->get('assigned_to'));
            }

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $tasks->whereBetween('created_at', [$request->get('date_from'), $request->get('date_to')]);
            }

            if ($request->filled('tags')) {
                $tags = explode(',', $request->get('tags'));
                $tasks->whereHas('tags', function ($query) use ($tags) {
                    $query->whereIn('tags.id', $tags);
                });
            }

            if ($request->filled('order_by')) {
                $dir = $request->get('order_dir', 'ASC');
                if (in_array($request->get('order_by', 'created_at'), ['created_at', 'due_date', 'priority', 'title'])) {
                    $tasks->orderBy($request->get('order_by'), $dir);
                }
            }

            $perPage = request()->input('length', 10);
            $page = (request()->input('start', 0) / $perPage) + 1;

            $tasks = $tasks->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Data retrieved successfully.',
                'recordsTotal' => $tasks->total(),
                'recordsFiltered' => $tasks->total(),
                'data' => $tasks->items()
            ]);
        } catch (\Exception $e) {
            Log::error("Tasks - getList: Exception in fetching tasks list: {$e->getMessage()}");
            return response()->json([
                'message' => 'Internal server error, please try again later!',
            ], 500);
        }
    }

    public function create(CreateTaskRequest $request) {
        if ($request->user()->cannot('create', Task::class)) {
            return response()->json([
                'message' => 'You do not have permission to create tasks.',
            ], 403);
        }

        DB::beginTransaction();
        try {
            $task = Task::create([
                'title'       => $request->title,
                'description' => $request->description,
                'status'      => $request->status ?? 'pending',
                'priority'    => $request->priority ?? 'medium',
                'due_date'    => $request->due_date,
                'assigned_to' => $request->assigned_to,
                'metadata'    => $request->metadata ? json_decode($request->metadata, true) : null
            ]);

            if ($request->filled('tags')) {
                $task->tags()->sync($request->tags);
            }

            DB::commit();
            return response()->json([
                'message' => 'Task created successfully.',
                'data'    => $task->load(['tags', 'assignedUser']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Tasks - create: Exception in creating new task: {$e->getMessage()}");

            return response()->json([
                'message' => 'Internal server error, please try again later!',
            ], 500);
        }
    }

    public function get(Request $request, $id) {
        try {
            $task = Task::with(['tags', 'assignedUser'])->find($id);

            if (!$task) {
                return response()->json([
                    'message' => 'Task not found.',
                ], 404);
            }

            if ($request->user()->cannot('view', $task)) {
                return response()->json([
                    'message' => 'You do not have permission to view this task.',
                ], 403);
            }

            return response()->json([
                'message' => 'Task retrieved successfully.',
                'data'    => $task,
            ]);
        } catch (\Exception $e) {
            Log::error("Tasks - getById: Exception in fetching task: {$e->getMessage()}");
            return response()->json([
                'message' => 'Internal server error, please try again later!',
            ], 500);
        }
    }

    public function update(UpdateTaskRequest $request, $id) {
        DB::beginTransaction();

        try {
            $task = Task::find($id);
            if (!$task) {
                return response()->json([
                    'message' => 'Task not found.'
                ], 404);
            }

            if ($request->user()->cannot('update', $task)) {
                return response()->json([
                    'message' => 'You do not have permission to update this task.',
                ], 403);
            }

            $updates = $request->only([
                'title', 'description', 'status', 'priority',
                'due_date', 'assigned_to'
            ]);

            $status = $request->status ?? $task->status;
            if (isset($updates['due_date']) && in_array($status, ['pending', 'in_progress'])) {
                $dueDate = Carbon::parse($updates['due_date'])->startOfDay();

                if ($dueDate->lt(now()->startOfDay())) {
                    return response()->json([
                        'message' => 'Due date cannot be in the past for pending or in-progress tasks.'
                    ], 400);
                }
            }

            if ($request->filled('metadata')) {
                $updates['metadata'] = json_decode($request->metadata, true);
            }

            $task->updateWithLock($updates, $request->version);

            if ($request->filled('tags')) {
                $task->tags()->sync($request->tags);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $task->refresh()->load(['tags', 'assignedUser']),
            ]);

        } catch (OptimisticLockException $e) {
            Log::error("Tasks - update: Optimistic Lock Exception when updating task #{$id}: {$e->getMessage()}");

            DB::rollBack();
            return response()->json([
                'message' => 'Conflict detected, this task was updated by another user',
            ], 409);

        } catch (\Exception $e) {
            Log::error("Tasks - update: Exception when updating task #{$id}: {$e->getMessage()}");

            DB::rollBack();
            return response()->json([
                'message' => 'Server error, please try again later!'
            ], 500);
        }
    }

    public function delete(Request $request, $id) {
        try {
            $task = Task::find($id);
            if (!$task) {
                return response()->json([
                    'message' => 'Task not found.'
                ], 404);
            }

            if ($request->user()->cannot('delete', $task)) {
                return response()->json([
                    'message' => 'You do not have permission to delete this task.',
                ], 403);
            }

            $task->delete();

            return response()->json([
                'message' => 'Task deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error("Tasks - delete: Exception in deleting task: {$e->getMessage()}");
            return response()->json([
                'message' => 'Internal server error, please try again later!',
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id) {
        $request->validate([
            'version' => 'required|integer'
        ]);

        DB::beginTransaction();
        try {
            $task = Task::find($id);
            if (!$task) {
                return response()->json([
                    'message' => 'Task not found.'
                ], 404);
            }

            if ($request->user()->cannot('update', $task)) {
                return response()->json([
                    'message' => 'You do not have permission to update this task.',
                ], 403);
            }

            $task->updateWithLock([
                'status' => $task->nextStatus()
            ], $request->version);

            DB::commit();
            return response()->json([
                'message' => 'Task status updated successfully.',
                'data'    => $task,
            ]);
        } catch (OptimisticLockException $e) {
            DB::rollBack();

            Log::error("Tasks - toggleStatus: Conflict detected, this task was updated by another user");
            return response()->json([
                'message' => 'Conflict detected, this task was updated by another user'
            ], 409);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Tasks - toggleStatus: Exception in updating task: {$e->getMessage()}");
            return response()->json([
                'message' => 'Internal server error, please try again later!',
            ], 500);
        }
    }

    public function restore(Request $request, $id) {
        try {
            $task = Task::withTrashed()->find($id);
            if (!$task) {
                return response()->json([
                    'message' => 'Task not found.'
                ], 404);
            } else if (!$task->deleted_at) {
                return response()->json([
                    'message' => 'Task already restored.'
                ], 400);
            }

            if ($request->user()->cannot('restore', $task)) {
                return response()->json([
                    'message' => 'You do not have permission to restore this task.',
                ], 403);
            }

            $task->restore();

            return response()->json([
                'message' => 'Task restored successfully.',
                'data'    => $task->load(['tags', 'assignedUser']),
            ]);
        } catch (\Exception $e) {
            Log::error("Tasks - restore: Exception in deleting task: {$e->getMessage()}");
            return response()->json([
                'message' => 'Internal server error, please try again later!',
            ], 500);
        }
    }
}
