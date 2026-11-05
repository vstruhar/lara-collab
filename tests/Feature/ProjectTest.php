<?php

use App\Models\Project;
use App\Models\User;
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
        'company' => $this->company,
        'project' => $this->project,
    ] = createProjectWithTask();
});

it('lists and opens the project forms', function () {
    $this->get(route('projects.index'))
        ->assertOk()
        ->assertSee('Acme Website')
        ->assertInertia(fn (Assert $page) => $page->component('Projects/Index'));

    $this->get(route('projects.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Projects/Create'));

    $this->get(route('projects.edit', $this->project))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Projects/Edit'));
});

it('creates a project with the default task groups', function () {
    $this->post(route('projects.store'), [
        'name' => 'New Website',
        'description' => 'Marketing site',
        'default_pricing_type' => 'hourly',
        'client_company_id' => $this->company->id,
        'rate' => 50,
        'users' => [],
    ])->assertRedirect(route('projects.index'))
        ->assertSessionHas('flash.type', 'success');

    $project = Project::where('name', 'New Website')->first();

    expect($project)->not->toBeNull()
        ->and($project->rate)->toBe(5000)
        ->and($project->taskGroups()->pluck('name')->all())->toEqualCanonicalizing([
            'Backlog',
            'Todo',
            'In progress',
            'QA',
            'Done',
            'Deployed',
        ]);
});

it('updates a project', function () {
    $this->put(route('projects.update', $this->project), [
        'name' => 'Acme Website v2',
        'description' => 'Updated',
        'default_pricing_type' => 'hourly',
        'client_company_id' => $this->company->id,
        'rate' => 75,
        'users' => [$this->admin->id],
    ])->assertRedirect(route('projects.index'))
        ->assertSessionHas('flash.type', 'success');

    $project = $this->project->fresh();

    expect($project->name)->toBe('Acme Website v2')
        ->and($project->rate)->toBe(7500)
        ->and($project->users()->pluck('users.id')->all())->toBe([$this->admin->id]);
});

it('archives and restores a project', function () {
    $this->from(route('projects.index'))
        ->delete(route('projects.destroy', $this->project))
        ->assertRedirect(route('projects.index'))
        ->assertSessionHas('flash.title', 'Project archived');

    expect(Project::withArchived()->find($this->project->id)->archived_at)->not->toBeNull();

    $this->from(route('projects.index'))
        ->post(route('projects.restore', $this->project->id))
        ->assertRedirect(route('projects.index'))
        ->assertSessionHas('flash.title', 'Project restored');

    expect(Project::find($this->project->id))->not->toBeNull();
});

it('toggles a project favorite', function () {
    $this->from(route('projects.index'))
        ->put(route('projects.favorite.toggle', $this->project))
        ->assertRedirect(route('projects.index'));

    expect($this->admin->fresh()->hasFavorited($this->project))->toBeTrue();
});

it('replaces project user access', function () {
    $member = User::factory()->create();

    $this->from(route('projects.edit', $this->project))
        ->post(route('projects.user_access', $this->project), [
            'users' => [$member->id],
            'clients' => [],
        ])->assertRedirect(route('projects.edit', $this->project));

    expect($this->project->users()->pluck('users.id')->all())->toBe([$member->id]);
});
