<?php

use App\Models\ClientCompany;
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
    $this->client = User::factory()->create();
});

it('lists companies and opens the forms', function () {
    ClientCompany::create(['name' => 'Acme Corp']);

    $this->get(route('clients.companies.index'))
        ->assertOk()
        ->assertSee('Acme Corp')
        ->assertInertia(fn (Assert $page) => $page->component('Clients/Companies/Index'));

    $this->get(route('clients.companies.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Clients/Companies/Create'));
});

it('creates a company and attaches clients', function () {
    $this->post(route('clients.companies.store'), [
        'name' => 'Globex',
        'clients' => [$this->client->id],
    ])->assertRedirect(route('clients.companies.index'))
        ->assertSessionHas('flash.title', 'Company created');

    $company = ClientCompany::where('name', 'Globex')->first();

    expect($company)->not->toBeNull()
        ->and($company->clients()->pluck('users.id')->all())->toBe([$this->client->id]);
});

it('updates a company', function () {
    $company = ClientCompany::create(['name' => 'Globex']);

    $this->get(route('clients.companies.edit', $company))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Clients/Companies/Edit'));

    $this->put(route('clients.companies.update', $company), [
        'name' => 'Globex International',
        'clients' => [$this->client->id],
    ])->assertRedirect(route('clients.companies.index'))
        ->assertSessionHas('flash.title', 'Company updated');

    expect($company->fresh()->name)->toBe('Globex International')
        ->and($company->clients()->pluck('users.id')->all())->toBe([$this->client->id]);
});

it('archives and restores a company', function () {
    $company = ClientCompany::create(['name' => 'Globex']);

    $this->from(route('clients.companies.index'))
        ->delete(route('clients.companies.destroy', $company))
        ->assertRedirect(route('clients.companies.index'))
        ->assertSessionHas('flash.title', 'Company archived');

    expect(ClientCompany::withArchived()->find($company->id)->archived_at)->not->toBeNull();

    $this->from(route('clients.companies.index'))
        ->post(route('clients.companies.restore', $company->id))
        ->assertRedirect(route('clients.companies.index'))
        ->assertSessionHas('flash.title', 'Company restored');

    expect(ClientCompany::find($company->id))->not->toBeNull();
});
