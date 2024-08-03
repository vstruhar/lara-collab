<?php

namespace App\Http\Controllers\Task;

use App\Events\TaskGroup\TaskGroupCreated;
use App\Events\TaskGroup\TaskGroupDeleted;
use App\Events\TaskGroup\TaskGroupOrderChanged;
use App\Events\TaskGroup\TaskGroupRestored;
use App\Events\TaskGroup\TaskGroupUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\TaskGroup\StoreTaskGroupRequest;
use App\Http\Requests\TaskGroup\UpdateTaskGroupRequest;
use App\Models\Project;
use App\Models\TaskGroup;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function store(StoreTaskGroupRequest $request, Project $project)
    {
        $this->authorize('create', [TaskGroup::class, $project]);

        $taskGroup = $project->taskGroups()->create($request->validated());

        TaskGroupCreated::dispatch($taskGroup);

        return redirect()->route('projects.tasks', $project)->success('Tasks group created', 'A new tasks group was successfully created.');
    }

    public function update(UpdateTaskGroupRequest $request, Project $project, TaskGroup $taskGroup)
    {
        $this->authorize('update', [$taskGroup, $project]);

        $taskGroup->update($request->validated());

        TaskGroupUpdated::dispatch($taskGroup);

        return redirect()->route('projects.tasks', $project)->success('Tasks group updated', 'The tasks group was successfully updated.');
    }

    public function destroy(Project $project, TaskGroup $taskGroup)
    {
        $this->authorize('delete', [$taskGroup, $project]);

        if ($taskGroup->tasks->isNotEmpty()) {
            return redirect()->route('projects.tasks', $project)->warning('Action stopped', 'You cannot archive a task group that still contains tasks.');
        }

        $taskGroup->archive();

        TaskGroupDeleted::dispatch($taskGroup->id, $project->id);

        return redirect()->route('projects.tasks', $project)->success('Tasks group archived', 'The tasks group was successfully archived.');
    }

    public function restore(Project $project, int $taskGroupId)
    {
        $taskGroup = TaskGroup::withArchived()->findOrFail($taskGroupId);

        $this->authorize('restore', [$taskGroup, $project]);

        $taskGroup->unArchive();

        TaskGroupRestored::dispatch($taskGroup);

        return redirect()->back()->success('Tasks group restored', 'The restoring of the tasks group was completed successfully.');
    }

    public function reorder(Request $request, Project $project)
    {
        $this->authorize('reorder', [TaskGroup::class, $project]);

        TaskGroup::setNewOrder($request->ids);

        TaskGroupOrderChanged::dispatch($project->id, $request->ids);

        return response()->json();
    }
}
