<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeLog\StoreTimeLogRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeLog;
use Illuminate\Http\JsonResponse;

class TimeLogController extends Controller
{
    public function store(StoreTimeLogRequest $request, Project $project, Task $task): JsonResponse
    {
        $timeLog = $task->timeLogs()->create(
            $request->validated() + ['user_id' => auth()->id()]
        );

        return response()->json(['timeLog' => $timeLog->load(['user:id,name'])]);
    }

    public function destroy(Project $project, Task $task, TimeLog $timeLog): JsonResponse
    {
        $timeLog->delete();

        return response()->json();
    }
}
