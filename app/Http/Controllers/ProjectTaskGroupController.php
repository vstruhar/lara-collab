<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskGroup\StoreTaskGroupRequest;
use App\Http\Requests\TaskGroup\UpdateTaskGroupRequest;
use App\Models\Project;
use App\Models\TaskGroup;
use Illuminate\Http\Request;

class ProjectTaskGroupController extends Controller
{
    /**
     * Store the specified resource in storage.
     */
    public function store(StoreTaskGroupRequest $request, Project $project)
    {
        $project->taskGroups()->create($request->validated());

        return redirect()->route('projects.tasks', $project)->success('Tasks group created', 'A new tasks group was successfully created.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskGroupRequest $request, Project $project, TaskGroup $taskGroup)
    {
        $taskGroup->update($request->validated());

        return redirect()->route('projects.tasks', $project)->success('Tasks group updated', 'The tasks group was successfully updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, TaskGroup $taskGroup)
    {
        if ($taskGroup->tasks->isNotEmpty()) {
            return redirect()->route('projects.tasks', $project)->warning('Action stopped', 'You cannot archive a task group that still contains tasks.');
        }

        $taskGroup->archive();

        return redirect()->route('projects.tasks', $project)->success('Tasks group archived', 'The tasks group was successfully archived.');
    }

    public function restore(Project $project, int $taskGroupId)
    {
        $taskGroup = TaskGroup::withArchived()->findOrFail($taskGroupId);

        $taskGroup->unArchive();

        return redirect()->route('projects.tasks', $project)->success('Tasks group restored', 'The restoring of the tasks group was completed successfully.');
    }

    public function reorder(Request $request, Project $project)
    {
        TaskGroup::setNewOrder($request->ids);

        return response()->json();
    }
}
