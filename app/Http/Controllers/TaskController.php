<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helper; // Import your helper class

class TaskController extends Controller
{

    public function index()
    {
        $tasks = Auth::user()->tasks;
        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in-progress,completed',
        ]);

        if (Auth::user()->role == Helper::USER_ROLE) {
            $task = Auth::user()->tasks()->create($request->all());

            return response()->json($task, 201);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }

    public function show($id)
    {
        if (Auth::user()->role == Helper::USER_ROLE) {
            $task = Auth::user()->tasks()->findOrFail($id);
            return response()->json($task);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }

    public function update(Request $request, $id)
    {
        // Validation
        $this->validate($request, [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:pending,in-progress,completed',
        ]);
        $user = Auth::user();
        if ($user->role == Helper::USER_ROLE) {
            $task = Task::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$task) {
                return response()->json(['message' => 'Task not found or you do not have permission to access this task'], 404);
            }
            $task->update($request->all());
            return response()->json($task);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->role == Helper::USER_ROLE) {
            $task = Task::where('id', $id)
                ->where('user_id', $user->id)
                ->first();
            if (!$task) {
                return response()->json(['message' => 'Task not found or you do not have permission to delete this task'], 404);
            }
            $task->delete();
            return response()->json(null, 204);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }


    public function adminIndex($userId)
    {
        if (Auth::user()->role == Helper::ADMIN_ROLE) {
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
            $tasks = Task::where('user_id', $userId)->get();
            return response()->json($tasks);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }
}
