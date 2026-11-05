<?php

use App\Models\User;
use App\Notifications\UserCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    resetPermissionServiceCache();
    seedAccessControl();
    createOwnerCompany();
    $this->admin = createAdmin();
    $this->actingAs($this->admin);
});

it('lists users and opens the forms', function () {
    $this->get(route('users.index'))
        ->assertOk()
        ->assertSee($this->admin->name)
        ->assertInertia(fn (Assert $page) => $page->component('Users/Index'));

    $this->get(route('users.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Users/Create'));
});

it('creates a user with an avatar', function () {
    Notification::fake();
    Storage::fake();

    $this->post(route('users.store'), [
        'job_title' => 'Designer',
        'name' => 'Grace Hopper',
        'phone' => '555-0101',
        'rate' => 40,
        'email' => 'grace.hopper@gmail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => ['developer'],
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ])->assertRedirect(route('users.index'))
        ->assertSessionHas('flash.title', 'User created');

    $user = User::where('email', 'grace.hopper@gmail.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->rate)->toBe(4000)
        ->and($user->hasRole('developer'))->toBeTrue()
        ->and($user->avatar)->toBe("/storage/avatars/{$user->id}.jpg");

    Notification::assertSentTo($user, UserCreatedNotification::class);
    Storage::assertExists("public/avatars/{$user->id}.jpg");
});

it('updates a user', function () {
    Notification::fake();
    Storage::fake();

    $this->post(route('users.store'), [
        'job_title' => 'Designer',
        'name' => 'Grace Hopper',
        'phone' => '555-0101',
        'rate' => 40,
        'email' => 'grace.hopper@gmail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => ['developer'],
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
    ]);

    $user = User::where('email', 'grace.hopper@gmail.com')->first();

    $this->get(route('users.edit', $user))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Users/Edit'));

    $this->put(route('users.update', $user), [
        'job_title' => 'Lead Designer',
        'name' => 'Grace Updated',
        'phone' => '555-0199',
        'rate' => 50,
        'email' => 'grace.hopper@gmail.com',
        'roles' => ['designer'],
        'avatar' => '',
    ])->assertRedirect(route('users.index'))
        ->assertSessionHas('flash.title', 'User updated');

    $user->refresh();

    expect($user->name)->toBe('Grace Updated')
        ->and($user->job_title)->toBe('Lead Designer')
        ->and($user->rate)->toBe(5000)
        ->and($user->hasRole('designer'))->toBeTrue();
});

it('archives and restores a user', function () {
    $user = User::factory()->create();
    $user->assignRole('developer');

    $this->from(route('users.index'))
        ->delete(route('users.destroy', $user))
        ->assertRedirect()
        ->assertSessionHas('flash.title', 'User archived');

    expect(User::withArchived()->find($user->id)->archived_at)->not->toBeNull();

    $this->from(route('users.index'))
        ->post(route('users.restore', $user->id))
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('flash.title', 'User restored');

    expect(User::find($user->id))->not->toBeNull();
});
