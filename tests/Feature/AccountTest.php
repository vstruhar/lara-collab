<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    resetPermissionServiceCache();
    seedAccessControl();
    createOwnerCompany();
    $this->admin = createAdmin();
    $this->actingAs($this->admin);
});

it('updates the signed-in profile', function () {
    Http::fake([
        '*' => Http::response('', 404),
    ]);

    $this->get(route('account.profile.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Account/Profile/Edit'));

    $this->from(route('account.profile.edit'))
        ->put(route('account.profile.update'), [
            'job_title' => 'Director',
            'name' => 'Ada Admin',
            'phone' => '555-0142',
            'email' => 'ada.admin@gmail.com',
            'avatar' => '',
        ])->assertRedirect(route('account.profile.edit'))
        ->assertSessionHas('flash.title', 'User updated');

    expect($this->admin->fresh()->name)->toBe('Ada Admin')
        ->and($this->admin->fresh()->job_title)->toBe('Director')
        ->and($this->admin->fresh()->phone)->toBe('555-0142');
});

it('lists notifications and marks them read', function () {
    $first = $this->admin->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\UserCreatedNotification',
        'data' => [
            'title' => 'Task assigned',
            'subtitle' => 'Homepage',
            'link' => '/dashboard',
        ],
    ]);

    $second = $this->admin->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\UserCreatedNotification',
        'data' => [
            'title' => 'Comment added',
            'subtitle' => 'Looks good',
            'link' => '/dashboard',
        ],
    ]);

    $this->get(route('notifications'))
        ->assertOk()
        ->assertSee('Task assigned')
        ->assertSee('Comment added')
        ->assertInertia(fn (Assert $page) => $page->component('Account/Notifications/Index'));

    $this->putJson(route('notifications.read', $first))->assertOk();

    expect($first->fresh()->read_at)->not->toBeNull()
        ->and($second->fresh()->read_at)->toBeNull();

    $this->putJson(route('notifications.read.all'))->assertOk();

    expect($second->fresh()->read_at)->not->toBeNull();
});
