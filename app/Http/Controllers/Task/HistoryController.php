<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request, Project $project, Task $task, TaskHistoryService $history): JsonResponse
    {
        if ((int) $task->project_id !== (int) $project->id) {
            abort(404);
        }

        $this->authorize('viewAny', [Task::class, $project]);

        return response()->json(
            $history->forTask($task, $request->user(), max(1, $request->integer('page', 1))),
        );
    }
}
