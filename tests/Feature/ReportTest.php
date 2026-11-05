<?php

use App\Models\OwnerCompany;
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
        'task' => $this->task,
    ] = createProjectWithTask();

    $this->company->update(['currency_id' => OwnerCompany::first()->currency_id]);

    $this->task->update([
        'assigned_to_user_id' => $this->admin->id,
        'pricing_type' => 'fixed',
        'fixed_price' => 2500,
        'completed_at' => now(),
        'billable' => true,
    ]);

    $this->task->timeLogs()->create([
        'user_id' => $this->admin->id,
        'minutes' => 90,
    ]);
});

it('shows the logged time sum report', function () {
    $this->get(route('reports.logged-time.sum'))
        ->assertOk()
        ->assertSee('Acme Website')
        ->assertInertia(fn (Assert $page) => $page->component('Reports/LoggedTimeSum'));
});

it('shows the daily logged time report', function () {
    $this->get(route('reports.logged-time.daily'))
        ->assertOk()
        ->assertSee($this->admin->name)
        ->assertInertia(fn (Assert $page) => $page->component('Reports/DailyLoggedTime'));
});

it('shows the fixed price sum report', function () {
    $this->get(route('reports.fixed-price.sum'))
        ->assertOk()
        ->assertSee($this->admin->name)
        ->assertInertia(fn (Assert $page) => $page->component('Reports/FixedPriceSum'));
});
