<?php

namespace App\Http\Controllers;

use App\Actions\Task\CreateTask;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Models\Label;
use App\Models\Project;
use App\Models\Task;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Project $project)
    {
        $this->authorize('viewAny', [Task::class, $project]);

        return Inertia::render('Projects/Tasks/Index', [
            'project' => $project,
            'usersWithAccessToProject' => PermissionService::usersWithAccessToProject($project),
            'labels' => Label::get(['id', 'name', 'color']),
            'taskGroups' => $project
                ->taskGroups()
                ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
                ->get(),
            'groupedTasks' => Task::where('project_id', $project->id)
                ->searchByQueryString()
                ->filterByQueryString()
                ->when($request->user()->hasRole('client'), fn ($query) => $query->where('hidden_from_clients', false))
                ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
                ->with([
                    'createdByUser:id,name',
                    'assignedToUser:id,name',
                    'subscribedUsers:id',
                    'labels:id,name,color',
                    'attachments',
                ])
                ->orderBy('completed_at')
                ->get()
                ->groupBy('group_id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request, Project $project)
    {
        (new CreateTask)->create($project, $request->validated());

        return redirect()->route('projects.tasks', $project)->success('Task added', 'A new task was successfully added.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function reorder(Request $request, Project $project)
    {
        $this->authorize('reorder', [Task::class, $project]);

        Task::setNewOrder($request->ids);

        return response()->json();
    }

    public function move(Request $request, Project $project)
    {
        $this->authorize('reorder', [Task::class, $project]);

        Task::setNewOrder($request->ids);

        Task::whereIn('id', $request->ids)->update(['group_id' => $request->group_id]);

        return response()->json();
    }

    public function complete(Request $request, Project $project, Task $task)
    {
        $this->authorize('complete', [Task::class, $project]);

        $task->update([
            'completed_at' => ($request->completed === true) ? now() : null,
        ]);

        return response()->json();
    }
}
