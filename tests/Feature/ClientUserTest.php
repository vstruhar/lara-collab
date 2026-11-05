<?php

use App\Models\ClientCompany;
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
    $this->company = ClientCompany::create(['name' => 'Acme Corp']);
});

it('lists client users and opens the forms', function () {
    $this->get(route('clients.users.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Clients/Users/Index'));

    $this->get(route('clients.users.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Clients/Users/Create'));
});

it('creates a client user with an avatar', function () {
    Notification::fake();
    Storage::fake();

    $this->post(route('clients.users.store'), [
        'name' => 'Ada Client',
        'phone' => '555-0100',
        'email' => 'ada.client@gmail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        'companies' => [$this->company->id],
    ])->assertRedirect(route('clients.users.index'))
        ->assertSessionHas('flash.title', 'Client created');

    $client = User::where('email', 'ada.client@gmail.com')->first();

    expect($client)->not->toBeNull()
        ->and($client->hasRole('client'))->toBeTrue()
        ->and($client->clientCompanies()->pluck('client_companies.id')->all())->toBe([$this->company->id])
        ->and($client->avatar)->toBe("/storage/avatars/{$client->id}.jpg");

    Notification::assertSentTo($client, UserCreatedNotification::class);
    Storage::assertExists("public/avatars/{$client->id}.jpg");
});

it('updates a client user', function () {
    Notification::fake();
    Storage::fake();

    $this->post(route('clients.users.store'), [
        'name' => 'Ada Client',
        'phone' => '555-0100',
        'email' => 'ada.client@gmail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        'companies' => [$this->company->id],
    ]);

    $client = User::where('email', 'ada.client@gmail.com')->first();

    $this->get(route('clients.users.edit', $client))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Clients/Users/Edit'));

    $this->put(route('clients.users.update', $client), [
        'name' => 'Ada Updated',
        'phone' => '555-0199',
        'email' => 'ada.client@gmail.com',
        'avatar' => '',
        'companies' => [$this->company->id],
    ])->assertRedirect(route('clients.users.index'))
        ->assertSessionHas('flash.title', 'Client updated');

    expect($client->fresh()->name)->toBe('Ada Updated')
        ->and($client->fresh()->phone)->toBe('555-0199');
});

it('archives and restores a client user', function () {
    $client = User::factory()->create();
    $client->assignRole('client');

    $this->from(route('clients.users.index'))
        ->delete(route('clients.users.destroy', $client))
        ->assertRedirect()
        ->assertSessionHas('flash.title', 'Client archived');

    expect(User::withArchived()->find($client->id)->archived_at)->not->toBeNull();

    $this->from(route('clients.users.index'))
        ->post(route('clients.users.restore', $client->id))
        ->assertRedirect(route('clients.users.index'))
        ->assertSessionHas('flash.title', 'Client restored');

    expect(User::find($client->id))->not->toBeNull();
});
