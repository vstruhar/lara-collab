<?php

namespace App\Http\Controllers;

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
            'assignees' => PermissionService::usersWithAccessToProject($project),
            'labels' => Label::all(),
            'taskGroups' => $project
                ->taskGroups()
                ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
                ->get(),
            'groupedTasks' => Task::where('project_id', $project->id)
                ->searchByQueryString()
                ->filterByQueryString()
                ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
                ->with([
                    'assignedToUser:id,name',
                    'labels:id,name,color',
                ])
                ->orderBy('completed_at')
                ->get()
                ->groupBy('group_id'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
