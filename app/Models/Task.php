<?php

namespace App\Models;

use App\Policies\TaskPolicy;
use App\Traits\HasAuditLogs;
use App\Traits\OptimisticLocking;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

#[UsePolicy(TaskPolicy::class)]
class Task extends Model
{
    use SoftDeletes, OptimisticLocking, HasAuditLogs, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'assigned_to',
        'version',
        'metadata'
    ];

    protected $hidden = [
        'updated_at', 'deleted_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'due_date' => 'date:Y-m-d',
    ];

    public function tags() {
        return $this->belongsToMany(Tag::class);
    }

    public function assignedUser() {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function nextStatus() {
        $statuses = ['pending', 'in_progress', 'completed'];
        $currentIdx = array_search($this->status, $statuses);
        $nextIndex = ($currentIdx + 1) % count($statuses);

        return $statuses[$nextIndex];
    }

    protected function logAudit($operation, $changes = [])
    {
        TaskLog::create([
            'task_id' => $this->id,
            'user_id' => Auth::check() ? Auth::id() : null,
            'changes' => $changes,
            'operation_type' => $operation,
        ]);
    }
}
