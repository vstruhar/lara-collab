<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskPriority\StoreTaskPriorityRequest;
use App\Http\Requests\TaskPriority\UpdateTaskPriorityRequest;
use App\Http\Resources\TaskPriorityResource;
use App\Models\TaskPriority;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskPriorityController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(TaskPriority::class, 'task_priority');
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Settings/Priorities/Index', [
            'items' => TaskPriorityResource::collection(
                TaskPriority::when($request->has('archived'), fn ($query) => $query->onlyArchived())
                    ->orderBy('order')
                    ->paginate(12)
            ),
        ]);
    }

    public function create()
    {
        return Inertia::render('Settings/Priorities/Create');
    }

    public function store(StoreTaskPriorityRequest $request)
    {
        TaskPriority::create($request->validated());

        return redirect()->route('settings.task-priorities.index')->success('Priority created', 'A new priority was successfully created.');
    }

    public function edit(TaskPriority $taskPriority)
    {
        return Inertia::render('Settings/Priorities/Edit', [
            'item' => new TaskPriorityResource($taskPriority),
        ]);
    }

    public function update(UpdateTaskPriorityRequest $request, TaskPriority $taskPriority)
    {
        $taskPriority->update($request->validated());

        return redirect()->route('settings.task-priorities.index')->success('Priority updated', 'The priority was successfully updated.');
    }

    public function destroy(TaskPriority $taskPriority)
    {
        $taskPriority->archive();

        return redirect()->back()->success('Priority archived', 'The priority was successfully archived.');
    }

    public function restore(int $priorityId)
    {
        $taskPriority = TaskPriority::withArchived()->findOrFail($priorityId);

        $this->authorize('restore', $taskPriority);

        $taskPriority->unArchive();

        return redirect()->back()->success('Priority restored', 'The restoring of the priority was completed successfully.');
    }
}
