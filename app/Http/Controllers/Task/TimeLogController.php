<?php

namespace App\Http\Controllers\Task;

use App\Events\Task\TimeLogCreated;
use App\Events\Task\TimeLogDeleted;
use App\Http\Controllers\Controller;
use App\Http\Requests\TimeLog\StoreTimeLogRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeLog;
use Illuminate\Http\JsonResponse;

class TimeLogController extends Controller
{
    public function startTimer(Project $project, Task $task): JsonResponse
    {
        $this->authorize('create', [TimeLog::class, $project]);

        $timeLog = $task->timeLogs()->create([
            'user_id' => auth()->id(),
            'minutes' => null,
            'timer_start' => now()->timestamp,
        ]);

        return response()->json(['timeLog' => $timeLog->load(['user:id,name'])]);
    }

    public function stopTimer(Project $project, Task $task, TimeLog $timeLog): JsonResponse
    {
        $this->authorize('create', [TimeLog::class, $project]);

        $timeLog->update([
            'timer_stop' => now()->timestamp,
            'minutes' => round((now()->timestamp - $timeLog->timer_start) / 60),
        ]);

        TimeLogCreated::dispatch($task, $timeLog);

        return response()->json(['timeLog' => $timeLog->load(['user:id,name'])]);
    }

    public function store(StoreTimeLogRequest $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('create', [TimeLog::class, $project]);

        $timeLog = $task->timeLogs()->create(
            $request->validated() + ['user_id' => auth()->id()]
        );

        TimeLogCreated::dispatch($task, $timeLog);

        return response()->json(['timeLog' => $timeLog->load(['user:id,name'])]);
    }

    public function destroy(Project $project, Task $task, TimeLog $timeLog): JsonResponse
    {
        $this->authorize('delete', [$timeLog, $project]);

        $timeLog->delete();

        TimeLogDeleted::dispatch($task, $timeLog->id);

        return response()->json();
    }
}
