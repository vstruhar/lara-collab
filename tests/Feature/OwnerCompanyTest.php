<?php

use App\Models\OwnerCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    resetPermissionServiceCache();
    seedAccessControl();
    $this->company = createOwnerCompany();
    $this->admin = createAdmin();
    $this->actingAs($this->admin);
});

it('shows and updates the owner company', function () {
    $this->get(route('settings.company.edit'))
        ->assertOk()
        ->assertSee('Agency')
        ->assertInertia(fn (Assert $page) => $page->component('Settings/Company/Edit'));

    $this->from(route('settings.company.edit'))
        ->put(route('settings.company.update'), [
            'name' => 'Northwind',
            'tax' => 10,
        ])->assertRedirect(route('settings.company.edit'))
        ->assertSessionHas('flash.title', 'Company updated');

    $company = OwnerCompany::first();

    expect($company->name)->toBe('Northwind')
        ->and($company->tax)->toBe(1000);
});
