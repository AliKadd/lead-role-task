<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TagsController extends Controller
{

    public function list(Request $request) {
        $tags = Tag::all();

        return response()->json([
            'message' => 'Retrieved successfully',
            'data' => $tags
        ]);
    }

    public function create(Request $request) {
        DB::beginTransaction();

        try {
            $request->validate([
                'name' => 'required',
                'color' => 'nullable|string',
            ]);

            $tag = Tag::create([
                'name' => $request->name,
                'color' => $request->color
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Tag created successfully.',
                'data'    => $tag,
            ], 201);
        } catch (\Exception $e) {
            Log::error("Tags - create: Exception in creating tag: {$e->getMessage()}");
            return response()->json([
                'message' => 'Server error, please try again later!',
            ], 500);
        }
    }

    public function update(Request $request, $id) {
        DB::beginTransaction();

        try {
            $request->validate([
                'name' => 'required',
                'color' => 'nullable|string',
            ]);

            $tag = Tag::find($id);
            if (!$tag) {
                return response()->json([
                    'message' => 'Tag not found.',
                ], 404);
            }

            $tag->update($request->only(['name', 'color']));

            DB::commit();
            return response()->json([
                'message' => 'Tag updated successfully.',
                'data'    => $tag,
            ]);
        } catch (\Exception $e) {
            Log::error("Tags - update: Exception in updating tag #{$id}: {$e->getMessage()}");
            return response()->json([
                'message' => 'Server error, please try again later!',
            ], 500);
        }
    }

    public function delete(Request $request, $id) {
        DB::beginTransaction();

        try {
            $tag = Tag::find($id);
            if (!$tag) {
                return response()->json([
                    'message' => 'Tag not found.',
                ], 404);
            }

            $tag->delete();

            DB::commit();
            return response()->json([
                'message' => 'Tag deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error("Tags - delete: Exception in deleting tag #{$id}: {$e->getMessage()}");
            return response()->json([
                'message' => 'Server error, please try again later!',
            ], 500);
        }
    }
}
