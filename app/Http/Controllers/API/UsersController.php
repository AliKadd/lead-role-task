<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{

    public function list(Request $request) {
        if ($request->user()->role === "admin") {
            $users = User::query()->whereNot("role", "admin")->get();
        } else {
            $users = [];
        }

        return response()->json([
            'message' => 'Retrieved successfully.',
            'data' => $users
        ]);
    }
}
