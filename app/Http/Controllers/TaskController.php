<?php

namespace App\Http\Controllers;

use App\Actions\Task\CreateTask;
use App\Actions\Task\UpdateTask;
use App\Events\Task\TaskDeleted;
use App\Events\Task\TaskRestored;
use App\Events\Task\TaskUpdated;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Label;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskGroup;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request, Project $project, Task $task = null): Response
    {
        $this->authorize('viewAny', [Task::class, $project]);

        $groups = $project
            ->taskGroups()
            ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
            ->get();

        $groupedTasks = $project
            ->taskGroups()
            ->get()
            ->mapWithKeys(function (TaskGroup $group) use ($request, $project) {
                return [
                    $group->id => Task::where('project_id', $project->id)
                        ->where('group_id', $group->id)
                        ->searchByQueryString()
                        ->filterByQueryString()
                        ->when($request->user()->hasRole('client'), fn ($query) => $query->where('hidden_from_clients', false))
                        ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
                        ->when(! $request->has('status'), fn ($query) => $query->whereNull('completed_at'))
                        ->withDefault()
                        ->get(),
                ];
            });

        return Inertia::render('Projects/Tasks/Index', [
            'project' => $project,
            'usersWithAccessToProject' => PermissionService::usersWithAccessToProject($project),
            'labels' => Label::get(['id', 'name', 'color']),
            'taskGroups' => $groups,
            'groupedTasks' => $groupedTasks,
            'openedTask' => $task ? $task->loadDefault() : null,
        ]);
    }

    public function store(StoreTaskRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('create', [Task::class, $project]);

        (new CreateTask)->create($project, $request->validated());

        return redirect()->route('projects.tasks', $project)->success('Task added', 'A new task was successfully added.');
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('update', [$task, $project]);

        (new UpdateTask)->update($task, $request->validated());

        $task->refresh()->load([
            'project:id,name',
            'createdByUser:id,name',
            'assignedToUser:id,name',
            'subscribedUsers:id',
            'labels:id,name,color',
            'attachments',
            'timeLogs.user:id,name',
        ]);

        return response()->json(['task' => $task]);
    }

    public function reorder(Request $request, Project $project): JsonResponse
    {
        $this->authorize('reorder', [Task::class, $project]);

        Task::setNewOrder($request->ids);

        return response()->json();
    }

    public function move(Request $request, Project $project): JsonResponse
    {
        $this->authorize('reorder', [Task::class, $project]);

        Task::setNewOrder($request->ids);

        Task::whereIn('id', $request->ids)->update(['group_id' => $request->group_id]);

        return response()->json();
    }

    public function complete(Request $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('complete', [Task::class, $project]);

        $task->update([
            'completed_at' => ($request->completed === true) ? now() : null,
        ]);

        TaskUpdated::dispatch($task);

        return response()->json();
    }

    public function destroy(Project $project, Task $task): RedirectResponse
    {
        $this->authorize('archive task', [$task, $project]);

        $task->archive();

        TaskDeleted::dispatch($task->id, $task->project_id);

        return redirect()->back()->success('Task archived', 'The task was successfully archived.');
    }

    public function restore(Project $project, int $taskId)
    {
        $task = Task::withArchived()->findOrFail($taskId);

        $this->authorize('restore', [$task, $project]);

        $task->unArchive();

        TaskRestored::dispatch($task);

        return redirect()->back()->success('Task restored', 'The restoring of the Task was completed successfully.');
    }
}
