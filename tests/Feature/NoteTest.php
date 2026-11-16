<?php

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    resetPermissionServiceCache();
    seedAccessControl();
    createOwnerCompany();
    $this->admin = createAdmin();
    $this->actingAs($this->admin);

    ['project' => $this->project] = createProjectWithTask();
});

it('lists, creates, updates, and deletes a note', function () {
    $this->post(route('projects.notes.store', $this->project), [
        'title' => 'Kickoff',
        'content' => 'Bring the brief',
    ])->assertRedirect(route('projects.notes', $this->project));

    $note = Note::where('title', 'Kickoff')->first();

    expect($note)->not->toBeNull()
        ->and($note->content)->toBe('Bring the brief');

    $this->get(route('projects.notes', $this->project))
        ->assertOk()
        ->assertSee('Kickoff')
        ->assertInertia(fn (Assert $page) => $page->component('Projects/Notes/Index'));

    $this->put(route('projects.notes.update', [$this->project, $note]), [
        'title' => 'Kickoff notes',
        'content' => 'Bring the revised brief',
    ])->assertRedirect(route('projects.notes', $this->project))
        ->assertSessionHas('flash.title', 'Note saved');

    expect($note->fresh()->title)->toBe('Kickoff notes');

    $this->delete(route('projects.notes.destroy', [$this->project, $note]))
        ->assertRedirect(route('projects.notes', $this->project))
        ->assertSessionHas('flash.title', 'Note deleted');

    expect(Note::find($note->id))->toBeNull();
});

it('strips unsafe html from notes and still encrypts locked content', function () {
    $this->post(route('projects.notes.store', $this->project), [
        'title' => 'Visible',
        'content' => '<p>Visible</p><script>alert(1)</script>',
    ])->assertRedirect(route('projects.notes', $this->project));

    $note = Note::where('title', 'Visible')->first();

    expect($note->content)->toBe('<p>Visible</p>')
        ->and($note->content)->not->toContain('<script')
        ->and($note->content)->not->toContain('alert');

    $legacy = $this->project->notes()->create([
        'title' => 'Legacy',
        'content' => '<img src=x onerror="alert(1)"><p>Kept</p>',
    ]);

    $this->get(route('projects.notes', $this->project))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('notes', function ($notes) use ($legacy) {
                $note = collect($notes)->firstWhere('id', $legacy->id);

                return is_array($note)
                    && str_contains($note['content'], 'Kept')
                    && ! str_contains($note['content'], 'onerror')
                    && ! str_contains($note['content'], '<img');
            }));

    expect($legacy->fresh()->getRawOriginal('content'))->toContain('onerror');

    $this->post(route('projects.notes.lock', [$this->project, $note]), [
        'passcode' => '1234',
        'content' => '<p>Secret</p><script>alert(1)</script>',
    ])->assertRedirect(route('projects.notes', $this->project));

    $note->refresh();

    expect($note->is_locked)->toBeTrue()
        ->and($note->content)->not->toContain('Secret')
        ->and($note->content)->not->toContain('<script')
        ->and($note->content)->not->toBe('<p>Secret</p>');

    $unlocked = $this->postJson(route('projects.notes.unlock', [$this->project, $note]), [
        'passcode' => '1234',
    ])->assertOk()->json('content');

    expect($unlocked)->toBe('<p>Secret</p>')
        ->and($unlocked)->not->toContain('<script')
        ->and($unlocked)->not->toContain('alert');
});

it('locks a note and unlocks it with the passcode', function () {
    $note = $this->project->notes()->create([
        'title' => 'Pricing',
        'content' => 'Draft',
    ]);

    $this->post(route('projects.notes.lock', [$this->project, $note]), [
        'passcode' => '1234',
        'content' => 'Secret notes',
    ])->assertRedirect(route('projects.notes', $this->project))
        ->assertSessionHas('flash.title', 'Note locked');

    $note->refresh();

    expect($note->is_locked)->toBeTrue()
        ->and($note->content)->not->toBe('Secret notes');

    $this->postJson(route('projects.notes.unlock', [$this->project, $note]), [
        'passcode' => '1234',
    ])->assertOk()
        ->assertJsonPath('content', 'Secret notes');
});

it('keeps the decrypted note when the lock is removed without new content', function () {
    $note = $this->project->notes()->create([
        'title' => 'Pricing',
        'content' => 'Draft',
    ]);

    $this->post(route('projects.notes.lock', [$this->project, $note]), [
        'passcode' => '1234',
        'content' => 'Secret notes',
    ])->assertRedirect();

    $this->post(route('projects.notes.remove-lock', [$this->project, $note]), [
        'passcode' => '1234',
    ])->assertRedirect(route('projects.notes', $this->project))
        ->assertSessionHas('flash.title', 'Lock removed');

    $note->refresh();

    expect($note->is_locked)->toBeFalse()
        ->and($note->passcode_salt)->toBeNull()
        ->and($note->content)->toBe('Secret notes');
});

it('stores sanitized empty content when removing a lock with only unsafe html', function () {
    $note = $this->project->notes()->create([
        'title' => 'Pricing',
        'content' => 'Draft',
    ]);

    $this->post(route('projects.notes.lock', [$this->project, $note]), [
        'passcode' => '1234',
        'content' => 'Secret notes',
    ])->assertRedirect();

    $this->post(route('projects.notes.remove-lock', [$this->project, $note]), [
        'passcode' => '1234',
        'content' => '<script>alert(1)</script><img src=x onerror="alert(1)">',
    ])->assertRedirect(route('projects.notes', $this->project))
        ->assertSessionHas('flash.title', 'Lock removed');

    $note->refresh();

    expect($note->is_locked)->toBeFalse()
        ->and($note->content)->toBe('')
        ->and($note->content)->not->toBe('Secret notes');
});

it('updates a locked note and then removes the lock', function () {
    $note = $this->project->notes()->create([
        'title' => 'Pricing',
        'content' => 'Draft',
    ]);

    $this->post(route('projects.notes.lock', [$this->project, $note]), [
        'passcode' => '1234',
        'content' => 'Secret notes',
    ]);

    $this->put(route('projects.notes.update', [$this->project, $note]), [
        'title' => 'Pricing revised',
        'content' => 'Updated secret',
        'passcode' => '1234',
    ])->assertRedirect(route('projects.notes', $this->project))
        ->assertSessionHas('flash.title', 'Note saved');

    $this->postJson(route('projects.notes.unlock', [$this->project, $note->fresh()]), [
        'passcode' => '1234',
    ])->assertOk()
        ->assertJsonPath('content', 'Updated secret');

    $this->post(route('projects.notes.remove-lock', [$this->project, $note]), [
        'passcode' => '1234',
        'content' => 'Now public',
    ])->assertRedirect(route('projects.notes', $this->project))
        ->assertSessionHas('flash.title', 'Lock removed');

    $note->refresh();

    expect($note->is_locked)->toBeFalse()
        ->and($note->passcode_salt)->toBeNull()
        ->and($note->content)->toBe('Now public');
});
