<?php

namespace App\Http\Controllers;

use App\Actions\Task\CreateTask;
use App\Actions\Task\UpdateTask;
use App\Events\Task\TaskDeleted;
use App\Events\Task\TaskGroupChanged;
use App\Events\Task\TaskOrderChanged;
use App\Events\Task\TaskRestored;
use App\Events\Task\TaskUpdated;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskPriorityResource;
use App\Models\Label;
use App\Models\OwnerCompany;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskGroup;
use App\Models\TaskPriority;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request, Project $project, ?Task $task = null): Response
    {
        $this->authorize('viewAny', [Task::class, $project]);

        $groups = $project
            ->taskGroups()
            ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
            ->get();

        $groupedTasks = $project
            ->taskGroups()
            ->with(['project' => fn ($query) => $query->withArchived()])
            ->get()
            ->mapWithKeys(function (TaskGroup $group) use ($request, $project) {
                $prioritySort = data_get($request->input('sort', []), 'priority');

                return [
                    $group->id => Task::where('project_id', $project->id)
                        ->where('group_id', $group->id)
                        ->searchByQueryString()
                        ->filterByQueryString()
                        ->visibleTo($request->user())
                        ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
                        ->when(! $request->has('status'), fn ($query) => $query->whereNull('completed_at'))
                        ->withDefault()
                        ->when($project->isArchived(), fn ($query) => $query->with(['project' => fn ($query) => $query->withArchived()]))
                        ->when($prioritySort, function ($query, $direction) {
                            $direction = $direction === 'asc' ? 'asc' : 'desc';

                            $query
                                ->leftJoin('task_priorities', 'tasks.priority_id', '=', 'task_priorities.id')
                                ->orderByRaw('tasks.priority_id IS NULL')
                                ->orderBy('task_priorities.order', $direction)
                                ->orderByDesc('tasks.created_at')
                                ->select('tasks.*');
                        }, function ($query) {
                            $query->orderByDesc('created_at');
                        })
                        ->get(),
                ];
            });

        return Inertia::render('Projects/Tasks/Index', [
            'project' => $project,
            'usersWithAccessToProject' => PermissionService::usersWithAccessToProject($project),
            'labels' => Label::get(['id', 'name', 'color']),
            'priorities' => TaskPriorityResource::collection(TaskPriority::orderBy('order')->get()),
            'taskGroups' => $groups,
            'groupedTasks' => $groupedTasks,
            'openedTask' => $task ? $task->loadDefault() : null,
            'currency' => [
                'symbol' => OwnerCompany::with('currency')->first()->currency->symbol,
            ],
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

        return response()->json();
    }

    public function reorder(Request $request, Project $project): JsonResponse
    {
        $this->authorize('reorder', [Task::class, $project]);

        Task::setNewOrder($this->taskIdsInProject($project, $request->input('ids', [])));

        TaskOrderChanged::dispatch(
            $project->id,
            $request->group_id,
            $request->from_index,
            $request->to_index,
        );

        return response()->json();
    }

    public function move(Request $request, Project $project): JsonResponse
    {
        $this->authorize('reorder', [Task::class, $project]);

        $taskIds = $this->taskIdsInProject($project, $request->input('ids', []));
        $groupBelongsToProject = $project->taskGroups()->whereKey($request->input('to_group_id'))->exists();

        if (! $groupBelongsToProject) {
            abort(404);
        }

        if ($taskIds !== []) {
            Task::setNewOrder($taskIds);
            Task::query()
                ->whereBelongsTo($project)
                ->whereIn('id', $taskIds)
                ->update(['group_id' => $request->input('to_group_id')]);
        }

        TaskGroupChanged::dispatch(
            $project->id,
            $request->from_group_id,
            $request->to_group_id,
            $request->from_index,
            $request->to_index,
        );

        return response()->json();
    }

    public function complete(Request $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('complete', [Task::class, $project]);

        $task->update([
            'completed_at' => ($request->completed === true) ? now() : null,
        ]);
        TaskUpdated::dispatch($task, 'completed_at');

        return response()->json();
    }

    public function destroy(Project $project, Task $task): RedirectResponse
    {
        $this->authorize('delete', [$task, $project]);

        $task->archive();
        TaskDeleted::dispatch($task->id, $task->project_id);

        return redirect()->back()->success('Task archived', 'The task was successfully archived.');
    }

    public function restore(Project $project, Task $task)
    {

        $this->authorize('restore', [$task, $project]);

        $task->unArchive();
        TaskRestored::dispatch($task);

        return redirect()->back()->success('Task restored', 'The restoring of the Task was completed successfully.');
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    private function taskIdsInProject(Project $project, array $ids): array
    {
        $requestedIds = collect($ids)->map(fn ($id) => (int) $id)->filter()->values();

        if ($requestedIds->isEmpty()) {
            return [];
        }

        $projectTaskIds = Task::query()
            ->whereBelongsTo($project)
            ->whereIn('id', $requestedIds)
            ->pluck('id');

        return $requestedIds
            ->filter(fn (int $id) => $projectTaskIds->contains($id))
            ->values()
            ->all();
    }
}
