<?php

use App\Events\Task\CommentCreated;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    resetPermissionServiceCache();
    seedAccessControl();
    createOwnerCompany();
    $this->admin = createAdmin();
    $this->actingAs($this->admin);

    [
        'project' => $this->project,
        'task' => $this->task,
    ] = createProjectWithTask();
});

it('lists and stores a comment', function () {
    Event::fake([CommentCreated::class]);

    $this->task->comments()->create([
        'user_id' => $this->admin->id,
        'content' => 'Already there',
    ]);

    $this->getJson(route('projects.tasks.comments', [$this->project, $this->task]))
        ->assertOk()
        ->assertJsonFragment(['content' => 'Already there']);

    $this->postJson(route('projects.tasks.comments.store', [$this->project, $this->task]), [
        'content' => 'Looks good to me',
    ])->assertOk()
        ->assertJsonPath('comment.content', 'Looks good to me');

    expect(Comment::where('content', 'Looks good to me')->exists())->toBeTrue();
    Event::assertDispatched(CommentCreated::class);
});
