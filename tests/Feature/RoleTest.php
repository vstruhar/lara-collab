<?php

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    resetPermissionServiceCache();
    seedAccessControl();
    createOwnerCompany();
    $this->admin = createAdmin();
    $this->actingAs($this->admin);
    $this->permission = Permission::query()->value('name');
});

it('lists roles and opens the forms', function () {
    $this->get(route('settings.roles.index'))
        ->assertOk()
        ->assertSee('admin')
        ->assertInertia(fn (Assert $page) => $page->component('Settings/Roles/Index'));

    $this->get(route('settings.roles.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Settings/Roles/Create'));
});

it('creates and updates a role', function () {
    $this->post(route('settings.roles.store'), [
        'name' => 'contractor',
        'permissions' => [$this->permission],
    ])->assertRedirect(route('settings.roles.index'))
        ->assertSessionHas('flash.title', 'Role created');

    $role = Role::where('name', 'contractor')->first();

    expect($role)->not->toBeNull()
        ->and($role->hasPermissionTo($this->permission))->toBeTrue();

    $this->get(route('settings.roles.edit', $role))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Settings/Roles/Edit'));

    $this->put(route('settings.roles.update', $role), [
        'name' => 'freelancer',
        'permissions' => [$this->permission],
    ])->assertRedirect(route('settings.roles.index'))
        ->assertSessionHas('flash.title', 'Role updated');

    expect($role->fresh()->name)->toBe('freelancer');
});

it('archives and restores an unused role', function () {
    $role = Role::create(['name' => 'contractor', 'guard_name' => 'web']);

    $this->from(route('settings.roles.index'))
        ->delete(route('settings.roles.destroy', $role))
        ->assertRedirect(route('settings.roles.index'))
        ->assertSessionHas('flash.title', 'Role archived');

    expect(Role::withArchived()->find($role->id)->archived_at)->not->toBeNull();

    $this->from(route('settings.roles.index'))
        ->post(route('settings.roles.restore', $role->id))
        ->assertRedirect(route('settings.roles.index'))
        ->assertSessionHas('flash.title', 'Role restored');

    expect(Role::find($role->id))->not->toBeNull();
});
