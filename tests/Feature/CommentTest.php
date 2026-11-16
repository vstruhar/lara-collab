<?php

use App\Events\Task\CommentCreated;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

it('strips unsafe html from a comment and keeps editor formatting', function () {
    $dangerous = '<p>Hello team</p><img src=x onerror="alert(1)"><script>alert(1)</script><a href="javascript:alert(1)">click</a>';

    $stripped = $this->postJson(route('projects.tasks.comments.store', [$this->project, $this->task]), [
        'content' => $dangerous,
    ])->assertOk()->json('comment.content');

    expect($stripped)->toContain('Hello team')
        ->and($stripped)->not->toContain('onerror')
        ->and($stripped)->not->toContain('<script')
        ->and($stripped)->not->toContain('javascript:');

    $stored = Comment::query()->latest('id')->first();

    expect($stored->getRawOriginal('content'))
        ->not->toContain('onerror')
        ->not->toContain('<script')
        ->not->toContain('javascript:')
        ->toContain('Hello team');

    $safe = '<p><strong>Looks good</strong></p><a href="https://example.com">Docs</a><span data-type="mention" class="mention" data-id="Ada">@Ada</span>';

    $formatted = $this->postJson(route('projects.tasks.comments.store', [$this->project, $this->task]), [
        'content' => $safe,
    ])->assertOk()->json('comment.content');

    expect($formatted)->toContain('<strong>Looks good</strong>')
        ->and($formatted)->toContain('https://example.com')
        ->and($formatted)->toContain('data-type="mention"')
        ->and($formatted)->toContain('data-id="Ada"')
        ->and($formatted)->toContain('@Ada');
});

it('rejects a comment that is empty after html is removed', function () {
    $this->postJson(route('projects.tasks.comments.store', [$this->project, $this->task]), [
        'content' => 'Hi<img src=x onerror="alert(1)">',
    ])->assertUnprocessable()
        ->assertJsonPath('message', 'The content field must be at least 8 characters.');

    expect(Comment::query()->count())->toBe(0);
});

it('cleans a comment that was stored before sanitization', function () {
    DB::table('comments')->insert([
        'user_id' => $this->admin->id,
        'task_id' => $this->task->id,
        'content' => '<script>alert(1)</script><p>Already there</p>',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->getJson(route('projects.tasks.comments', [$this->project, $this->task]))
        ->assertOk()
        ->assertJsonFragment(['content' => '<p>Already there</p>']);
});

it('lets a client with project access create a comment', function () {
    $client = User::factory()->create();
    $client->assignRole('client');
    $client->clientCompanies()->attach($this->project->client_company_id);

    $this->actingAs($client)
        ->postJson(route('projects.tasks.comments.store', [$this->project, $this->task]), [
            'content' => 'Client comment',
        ])->assertOk()
        ->assertJsonPath('comment.content', 'Client comment');
});
