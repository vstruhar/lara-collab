<?php

use App\Events\Task\TaskCreated;
use App\Events\Task\TaskDeleted;
use App\Events\Task\TaskGroupChanged;
use App\Events\Task\TaskOrderChanged;
use App\Events\Task\TaskRestored;
use App\Events\Task\TaskUpdated;
use App\Models\Task;
use App\Models\TaskGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    resetPermissionServiceCache();
    seedAccessControl();
    createOwnerCompany();
    $this->admin = createAdmin();
    $this->actingAs($this->admin);

    [
        'project' => $this->project,
        'group' => $this->group,
        'task' => $this->task,
    ] = createProjectWithTask();
});

it('shows the task board and an open task', function () {
    $this->get(route('projects.tasks', $this->project))
        ->assertOk()
        ->assertSee('Homepage')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Tasks/Index')
            ->where('openedTask', null));

    $this->get(route('projects.tasks.open', [$this->project, $this->task]))
        ->assertOk()
        ->assertSee('Homepage')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Tasks/Index')
            ->where('openedTask.id', $this->task->id));
});

it('creates a task', function () {
    Event::fake([TaskCreated::class]);

    $this->post(route('projects.tasks.store', $this->project), [
        'name' => 'Write copy',
        'group_id' => $this->group->id,
        'assigned_to_user_id' => null,
        'description' => 'Homepage hero',
        'due_on' => null,
        'estimation' => null,
        'priority_id' => null,
        'pricing_type' => 'hourly',
        'fixed_price' => null,
        'hidden_from_clients' => false,
        'billable' => true,
        'subscribed_users' => [],
        'labels' => [],
        'attachments' => [],
    ])->assertRedirect(route('projects.tasks', $this->project))
        ->assertSessionHas('flash.title', 'Task added');

    expect(Task::where('name', 'Write copy')->first())->not->toBeNull();
    Event::assertDispatched(TaskCreated::class);
});

it('updates a task', function () {
    Event::fake([TaskUpdated::class]);

    $this->putJson(route('projects.tasks.update', [$this->project, $this->task]), [
        'name' => 'Homepage refresh',
    ])->assertOk();

    expect($this->task->fresh()->name)->toBe('Homepage refresh');
    Event::assertDispatched(TaskUpdated::class);
});

it('completes and reopens a task', function () {
    Event::fake([TaskUpdated::class]);

    $this->postJson(route('projects.tasks.complete', [$this->project, $this->task]), [
        'completed' => true,
    ])->assertOk();

    expect($this->task->fresh()->completed_at)->not->toBeNull();

    $this->postJson(route('projects.tasks.complete', [$this->project, $this->task]), [
        'completed' => false,
    ])->assertOk();

    expect($this->task->fresh()->completed_at)->toBeNull();
    Event::assertDispatched(TaskUpdated::class);
});

it('reorders and moves a task', function () {
    Event::fake([TaskOrderChanged::class, TaskGroupChanged::class]);

    $second = Task::create([
        'project_id' => $this->project->id,
        'group_id' => $this->group->id,
        'created_by_user_id' => $this->admin->id,
        'name' => 'Footer',
        'number' => 2,
    ]);

    $this->postJson(route('projects.tasks.reorder', $this->project), [
        'ids' => [$second->id, $this->task->id],
        'group_id' => $this->group->id,
        'task_id' => $second->id,
    ])->assertOk();

    Event::assertDispatched(TaskOrderChanged::class);

    $destination = TaskGroup::create([
        'project_id' => $this->project->id,
        'name' => 'Doing',
    ]);

    $this->postJson(route('projects.tasks.move', $this->project), [
        'ids' => [$this->task->id],
        'from_group_id' => $this->group->id,
        'to_group_id' => $destination->id,
        'task_id' => $this->task->id,
    ])->assertOk();

    expect($this->task->fresh()->group_id)->toBe($destination->id);
    Event::assertDispatched(TaskGroupChanged::class);
});

it('archives and restores a task', function () {
    Event::fake([TaskDeleted::class, TaskRestored::class]);

    $this->from(route('projects.tasks', $this->project))
        ->delete(route('projects.tasks.destroy', [$this->project, $this->task]))
        ->assertRedirect(route('projects.tasks', $this->project))
        ->assertSessionHas('flash.title', 'Task archived');

    expect(Task::withArchived()->find($this->task->id)->archived_at)->not->toBeNull();

    $this->from(route('projects.tasks', $this->project))
        ->post(route('projects.tasks.restore', [$this->project, $this->task]))
        ->assertRedirect(route('projects.tasks', $this->project))
        ->assertSessionHas('flash.title', 'Task restored');

    expect(Task::find($this->task->id))->not->toBeNull();
    Event::assertDispatched(TaskDeleted::class);
    Event::assertDispatched(TaskRestored::class);
});
