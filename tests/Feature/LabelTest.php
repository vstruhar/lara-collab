<?php

use App\Models\Label;
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

it('lists labels and opens the forms', function () {
    Label::create(['name' => 'Bug', 'color' => '#ff0000']);

    $this->get(route('settings.labels.index'))
        ->assertOk()
        ->assertSee('Bug')
        ->assertInertia(fn (Assert $page) => $page->component('Settings/Labels/Index'));

    $this->get(route('settings.labels.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Settings/Labels/Create'));
});

it('creates and updates a label', function () {
    $this->post(route('settings.labels.store'), [
        'name' => 'Bug',
        'color' => '#ff0000',
    ])->assertRedirect(route('settings.labels.index'))
        ->assertSessionHas('flash.title', 'Label created');

    $label = Label::where('name', 'Bug')->first();

    expect($label)->not->toBeNull();

    $this->get(route('settings.labels.edit', $label))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Settings/Labels/Edit'));

    $this->put(route('settings.labels.update', $label), [
        'name' => 'Defect',
        'color' => '#00aa00',
    ])->assertRedirect(route('settings.labels.index'))
        ->assertSessionHas('flash.title', 'Label updated');

    expect($label->fresh()->name)->toBe('Defect')
        ->and($label->fresh()->color)->toBe('#00aa00');
});

it('archives and restores a label', function () {
    $label = Label::create(['name' => 'Bug', 'color' => '#ff0000']);

    $this->from(route('settings.labels.index'))
        ->delete(route('settings.labels.destroy', $label))
        ->assertRedirect(route('settings.labels.index'))
        ->assertSessionHas('flash.title', 'Label archived');

    expect(Label::withArchived()->find($label->id)->archived_at)->not->toBeNull();

    $this->from(route('settings.labels.index'))
        ->post(route('settings.labels.restore', $label->id))
        ->assertRedirect(route('settings.labels.index'))
        ->assertSessionHas('flash.title', 'Label restored');

    expect(Label::find($label->id))->not->toBeNull();
});
