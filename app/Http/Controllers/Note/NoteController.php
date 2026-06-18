<?php

namespace App\Http\Controllers\Note;

use App\Http\Controllers\Controller;
use App\Http\Requests\Note\LockNoteRequest;
use App\Http\Requests\Note\RemoveLockNoteRequest;
use App\Http\Requests\Note\StoreNoteRequest;
use App\Http\Requests\Note\UnlockNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Models\Note;
use App\Models\Project;
use App\Services\NoteEncryption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NoteController extends Controller
{
    public function index(Request $request, Project $project): Response
    {
        $this->authorize('viewAny', [Note::class, $project]);

        $notes = $project->notes()
            ->latest('updated_at')
            ->get(['id', 'project_id', 'title', 'content', 'is_locked', 'created_at', 'updated_at'])
            ->each(function (Note $note) {
                if ($note->is_locked) {
                    $note->setAttribute('content', null);
                }
            });

        return Inertia::render('Projects/Notes/Index', [
            'project' => $project,
            'notes' => $notes,
        ]);
    }

    public function store(StoreNoteRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('create', [Note::class, $project]);

        $project->notes()->create($request->validated());

        return redirect()->route('projects.notes', $project);
    }

    public function update(UpdateNoteRequest $request, Project $project, Note $note): RedirectResponse
    {
        $this->authorize('update', [$note, $project]);

        $validated = $request->validated();

        if ($note->is_locked) {
            if (! NoteEncryption::isValidPasscode($validated['passcode'], $note->passcode_salt, $note->content)) {
                return redirect()->back()->error('Incorrect passcode', 'The passcode you entered is incorrect.');
            }

            $note->update([
                'title' => $validated['title'],
                'content' => NoteEncryption::encrypt($validated['passcode'], $note->passcode_salt, $validated['content'] ?? ''),
            ]);
        } else {
            $note->update([
                'title' => $validated['title'],
                'content' => $validated['content'] ?? '',
            ]);
        }

        return redirect()->route('projects.notes', $project)->success('Note saved', 'The note was successfully saved.');
    }

    public function lock(LockNoteRequest $request, Project $project, Note $note): RedirectResponse
    {
        $this->authorize('update', [$note, $project]);

        if ($note->is_locked) {
            return redirect()->back()->error('Note is already locked', 'This note is already locked.');
        }

        $validated = $request->validated();
        $salt = NoteEncryption::generateSalt();

        $note->update([
            'content' => NoteEncryption::encrypt($validated['passcode'], $salt, $validated['content'] ?? ''),
            'is_locked' => true,
            'passcode_salt' => $salt,
        ]);

        return redirect()->route('projects.notes', $project)->success('Note locked', 'The note is now locked with your passcode.');
    }

    public function unlock(UnlockNoteRequest $request, Project $project, Note $note): JsonResponse
    {
        $this->authorize('viewAny', [Note::class, $project]);

        if (! NoteEncryption::isValidPasscode($request->validated('passcode'), $note->passcode_salt, $note->content)) {
            return response()->json(['message' => 'Incorrect passcode'], 422);
        }

        return response()->json([
            'content' => NoteEncryption::decrypt($request->validated('passcode'), $note->passcode_salt, $note->content),
        ]);
    }

    public function removeLock(RemoveLockNoteRequest $request, Project $project, Note $note): RedirectResponse
    {
        $this->authorize('update', [$note, $project]);

        if (! NoteEncryption::isValidPasscode($request->validated('passcode'), $note->passcode_salt, $note->content)) {
            return redirect()->back()->error('Incorrect passcode', 'The passcode you entered is incorrect.');
        }

        $decrypted = NoteEncryption::decrypt($request->validated('passcode'), $note->passcode_salt, $note->content);

        $note->update([
            'content' => $request->validated('content') ?? $decrypted,
            'is_locked' => false,
            'passcode_salt' => null,
        ]);

        return redirect()->route('projects.notes', $project)->success('Lock removed', 'The note is no longer locked.');
    }

    public function destroy(Project $project, Note $note): RedirectResponse
    {
        $this->authorize('delete', [$note, $project]);

        $note->delete();

        return redirect()->route('projects.notes', $project)->success('Note deleted', 'The note was successfully deleted.');
    }
}
