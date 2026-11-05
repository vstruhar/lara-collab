<?php

use App\Models\Invoice;
use App\Models\OwnerCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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
    $this->task->timeLogs()->create([
        'user_id' => $this->admin->id,
        'minutes' => 60,
    ]);
});

function invoicePayload(string $number): array
{
    return [
        'number' => $number,
        'client_company_id' => test()->company->id,
        'projects' => [test()->project->id],
        'tasks' => [test()->task->id],
        'type' => 'default',
        'hourly_rate' => 100,
        'note' => 'Thanks for the work',
    ];
}

it('lists invoices and opens the create form', function () {
    $this->get(route('invoices.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Invoices/Index'));

    $this->get(route('invoices.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Invoices/Create')
            ->where('nextNumber', today()->year.'0001'));
});

it('creates an invoice and stores its pdf', function () {
    Storage::fake('invoices');

    $this->post(route('invoices.store'), invoicePayload('20260001'))
        ->assertRedirect(route('invoices.index'))
        ->assertSessionHas('flash.title', 'Invoice created');

    $invoice = Invoice::where('number', '20260001')->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->filename)->not->toBeNull()
        ->and($invoice->status)->toBe('new')
        ->and($this->task->fresh()->invoice_id)->toBe($invoice->id);

    Storage::disk('invoices')->assertExists($invoice->filename);
});

it('updates an invoice, changes its status, and serves the pdf', function () {
    Storage::fake('invoices');

    $this->post(route('invoices.store'), invoicePayload('20260001'));
    $invoice = Invoice::where('number', '20260001')->first();

    $this->get(route('invoices.edit', $invoice))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Invoices/Edit'));

    $this->put(route('invoices.update', $invoice), invoicePayload('20260002'))
        ->assertRedirect(route('invoices.index'))
        ->assertSessionHas('flash.title', 'Invoice updated');

    $invoice->refresh();

    expect($invoice->number)->toBe('20260002');
    Storage::disk('invoices')->assertExists($invoice->filename);

    $this->from(route('invoices.index'))
        ->put(route('invoices.status', $invoice), ['status' => 'paid'])
        ->assertRedirect(route('invoices.index'));

    expect($invoice->fresh()->status)->toBe('paid');

    $this->get(route('invoices.pdf', $invoice))->assertOk();
    $this->get(route('invoices.download', $invoice))->assertOk();
});

it('archives and restores an invoice', function () {
    Storage::fake('invoices');

    $this->post(route('invoices.store'), invoicePayload('20260001'));
    $invoice = Invoice::where('number', '20260001')->first();

    $this->from(route('invoices.index'))
        ->delete(route('invoices.destroy', $invoice))
        ->assertRedirect(route('invoices.index'))
        ->assertSessionHas('flash.title', 'Invoice archived');

    expect(Invoice::withArchived()->find($invoice->id)->archived_at)->not->toBeNull();

    $this->from(route('invoices.index'))
        ->post(route('invoices.restore', $invoice->id))
        ->assertRedirect(route('invoices.index'))
        ->assertSessionHas('flash.title', 'Invoice restored');

    expect(Invoice::find($invoice->id))->not->toBeNull();
});
