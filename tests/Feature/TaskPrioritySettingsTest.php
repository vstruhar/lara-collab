<?php

use App\Models\TaskPriority;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    resetPermissionServiceCache();
    seedAccessControl();
    createOwnerCompany();
    $this->admin = createAdmin();
    $this->actingAs($this->admin);
});

it('lists priorities and opens the forms', function () {
    TaskPriority::create(['label' => 'High', 'color' => '#ff0000', 'order' => 1]);

    $this->get(route('settings.task-priorities.index'))
        ->assertOk()
        ->assertSee('High')
        ->assertInertia(fn (Assert $page) => $page->component('Settings/Priorities/Index'));

    $this->get(route('settings.task-priorities.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Settings/Priorities/Create'));
});

it('creates and updates a priority', function () {
    $this->post(route('settings.task-priorities.store'), [
        'label' => 'Urgent',
        'color' => '#ff0000',
        'order' => 1,
    ])->assertRedirect(route('settings.task-priorities.index'))
        ->assertSessionHas('flash.title', 'Priority created');

    $priority = TaskPriority::where('label', 'Urgent')->first();

    expect($priority)->not->toBeNull();

    $this->get(route('settings.task-priorities.edit', $priority))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Settings/Priorities/Edit'));

    $this->put(route('settings.task-priorities.update', $priority), [
        'label' => 'Critical',
        'color' => '#aa0000',
        'order' => 2,
    ])->assertRedirect(route('settings.task-priorities.index'))
        ->assertSessionHas('flash.title', 'Priority updated');

    expect($priority->fresh()->label)->toBe('Critical')
        ->and($priority->fresh()->order)->toBe(2);
});

it('archives and restores a priority', function () {
    $priority = TaskPriority::create([
        'label' => 'Urgent',
        'color' => '#ff0000',
        'order' => 1,
    ]);

    $this->from(route('settings.task-priorities.index'))
        ->delete(route('settings.task-priorities.destroy', $priority))
        ->assertRedirect(route('settings.task-priorities.index'))
        ->assertSessionHas('flash.title', 'Priority archived');

    expect(TaskPriority::find($priority->id))->toBeNull()
        ->and(TaskPriority::withArchived()->find($priority->id)->archived_at)->not->toBeNull();

    $this->get(route('settings.task-priorities.index', ['archived' => 1]))
        ->assertOk()
        ->assertSee('Urgent');

    $this->from(route('settings.task-priorities.index', ['archived' => 1]))
        ->post(route('settings.task-priorities.restore', $priority->id))
        ->assertRedirect(route('settings.task-priorities.index', ['archived' => 1]))
        ->assertSessionHas('flash.title', 'Priority restored');

    expect(TaskPriority::find($priority->id))->not->toBeNull();
});
