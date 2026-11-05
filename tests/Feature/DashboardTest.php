<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
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
        'task' => $this->task,
    ] = createProjectWithTask();
});

it('shows the dashboard with an overdue task', function () {
    $this->task->update([
        'assigned_to_user_id' => $this->admin->id,
        'due_on' => now()->subDay()->toDateString(),
    ]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Index')
            ->has('projects', 1)
            ->has('overdueTasks', 1)
            ->has('recentlyAssignedTasks')
            ->has('recentComments'));
});

it('shows tasks assigned to the current user', function () {
    $this->task->update(['assigned_to_user_id' => $this->admin->id]);

    $this->get(route('my-work.tasks.index'))
        ->assertOk()
        ->assertSee('Homepage')
        ->assertInertia(fn (Assert $page) => $page->component('MyWork/Tasks/Index'));
});

it('shows the activity feed', function () {
    $this->get(route('my-work.activity.index'))
        ->assertOk()
        ->assertSee('Homepage')
        ->assertInertia(fn (Assert $page) => $page->component('MyWork/Activity/Index'));
});

it('returns the requested dropdown values', function () {
    $this->getJson(route('dropdown.values', [
        'users' => true,
        'clients' => true,
        'mentionProjectUsers' => true,
        'projectId' => $this->project->id,
    ]))
        ->assertOk()
        ->assertJsonFragment(['label' => $this->admin->name])
        ->assertJsonPath('mentionProjectUsers.0', $this->admin->name)
        ->assertJsonStructure(['users', 'clients', 'mentionProjectUsers']);
});
