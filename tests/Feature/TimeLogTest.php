<?php

use App\Events\Task\TimeLogCreated;
use App\Events\Task\TimeLogDeleted;
use App\Models\TimeLog;
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

it('stores and deletes a time log', function () {
    Event::fake([TimeLogCreated::class, TimeLogDeleted::class]);

    $response = $this->postJson(route('projects.tasks.time-logs.store', [$this->project, $this->task]), [
        'minutes' => 45,
    ])->assertOk()
        ->assertJsonPath('timeLog.minutes', 45);

    $timeLog = TimeLog::find($response->json('timeLog.id'));

    expect($timeLog)->not->toBeNull()
        ->and($timeLog->user_id)->toBe($this->admin->id);

    $this->deleteJson(route('projects.tasks.time-logs.destroy', [$this->project, $this->task, $timeLog]))
        ->assertOk();

    expect(TimeLog::find($timeLog->id))->toBeNull();
    Event::assertDispatched(TimeLogCreated::class);
    Event::assertDispatched(TimeLogDeleted::class);
});

it('starts and stops a timer', function () {
    Event::fake([TimeLogCreated::class]);

    $response = $this->postJson(route('projects.tasks.time-logs.timer.start', [$this->project, $this->task]))
        ->assertOk();

    $timeLog = TimeLog::find($response->json('timeLog.id'));

    expect($timeLog->timer_start)->not->toBeNull()
        ->and($timeLog->minutes)->toBeNull();

    $this->travel(5)->minutes();

    $this->postJson(route('projects.tasks.time-logs.timer.stop', [$this->project, $this->task, $timeLog]))
        ->assertOk();

    $timeLog->refresh();

    expect($timeLog->timer_stop)->not->toBeNull()
        ->and($timeLog->minutes)->toBe(5);

    Event::assertDispatched(TimeLogCreated::class);
});
