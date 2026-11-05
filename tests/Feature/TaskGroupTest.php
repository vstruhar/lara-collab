<?php

use App\Events\TaskGroup\TaskGroupCreated;
use App\Events\TaskGroup\TaskGroupDeleted;
use App\Events\TaskGroup\TaskGroupOrderChanged;
use App\Events\TaskGroup\TaskGroupRestored;
use App\Events\TaskGroup\TaskGroupUpdated;
use App\Models\TaskGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    resetPermissionServiceCache();
    seedAccessControl();
    createOwnerCompany();
    $this->admin = createAdmin();
    $this->actingAs($this->admin);

    ['project' => $this->project] = createProjectWithTask();
});

it('creates a task group', function () {
    Event::fake([TaskGroupCreated::class]);

    $this->post(route('projects.task-groups.store', $this->project), [
        'name' => 'Review',
        'color' => '#112233',
    ])->assertRedirect(route('projects.tasks', $this->project))
        ->assertSessionHas('flash.title', 'Tasks group created');

    $group = TaskGroup::where('name', 'Review')->first();

    expect($group)->not->toBeNull()
        ->and($group->project_id)->toBe($this->project->id)
        ->and($group->color)->toBe('#112233');

    Event::assertDispatched(TaskGroupCreated::class);
});

it('updates a task group', function () {
    Event::fake([TaskGroupUpdated::class]);

    $group = TaskGroup::create([
        'project_id' => $this->project->id,
        'name' => 'Review',
    ]);

    $this->put(route('projects.task-groups.update', [$this->project, $group]), [
        'name' => 'Ready for review',
        'color' => '#445566',
    ])->assertRedirect(route('projects.tasks', $this->project))
        ->assertSessionHas('flash.title', 'Tasks group updated');

    expect($group->fresh()->name)->toBe('Ready for review');
    Event::assertDispatched(TaskGroupUpdated::class);
});

it('archives an empty task group and restores it', function () {
    Event::fake([TaskGroupDeleted::class, TaskGroupRestored::class]);

    $group = TaskGroup::create([
        'project_id' => $this->project->id,
        'name' => 'Empty',
    ]);

    $this->from(route('projects.tasks', $this->project))
        ->delete(route('projects.task-groups.destroy', [$this->project, $group]))
        ->assertRedirect(route('projects.tasks', $this->project))
        ->assertSessionHas('flash.title', 'Tasks group archived');

    expect(TaskGroup::withArchived()->find($group->id)->archived_at)->not->toBeNull();

    $this->from(route('projects.tasks', $this->project))
        ->post(route('projects.task-groups.restore', [$this->project, $group->id]))
        ->assertRedirect(route('projects.tasks', $this->project))
        ->assertSessionHas('flash.title', 'Tasks group restored');

    expect(TaskGroup::find($group->id))->not->toBeNull();
    Event::assertDispatched(TaskGroupDeleted::class);
    Event::assertDispatched(TaskGroupRestored::class);
});

it('reorders task groups', function () {
    Event::fake([TaskGroupOrderChanged::class]);

    $second = TaskGroup::create([
        'project_id' => $this->project->id,
        'name' => 'Doing',
    ]);

    $first = $this->project->taskGroups()->where('name', 'To Do')->first();

    $this->postJson(route('projects.task-groups.reorder', $this->project), [
        'ids' => [$second->id, $first->id],
    ])->assertOk();

    expect($second->fresh()->order_column)->toBeLessThan($first->fresh()->order_column);
    Event::assertDispatched(TaskGroupOrderChanged::class);
});
