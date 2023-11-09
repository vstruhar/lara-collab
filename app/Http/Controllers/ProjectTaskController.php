<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        return Inertia::render('Projects/Tasks/Index', [
            'project' => $project,
            'taskGroups' => $project->taskGroups,
            'groupedTasks' => Task::where('project_id', $project->id)
                ->searchByQueryString()
                ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
                ->with(['assignedToUser:id,name'])
                ->orderBy('completed_at')
                ->get()
                ->groupBy('task_group_id'),
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
     * Display the specified resource.
     */
    public function show(string $id)
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
        Task::setNewOrder($request->ids);

        return response()->json();
    }

    public function move(Request $request, Project $project)
    {
        Task::setNewOrder($request->ids);

        Task::whereIn('id', $request->ids)->update(['task_group_id' => $request->group_id]);

        return response()->json();
    }

    public function complete(Request $request, Project $project, Task $task)
    {
        $task->update([
            'completed_at' => ($request->completed === true) ? now() : null,
        ]);

        return response()->json();
    }
}
