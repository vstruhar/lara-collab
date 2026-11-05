<?php

use App\Actions\Task\UpdateTask;
use App\Events\Task\CommentCreated;
use App\Events\Task\TaskCreated;
use App\Events\Task\TaskDeleted;
use App\Events\Task\TaskGroupChanged;
use App\Events\Task\TaskOrderChanged;
use App\Events\Task\TaskUpdated;
use App\Events\Task\TaskVisibilityChanged;
use App\Events\TaskGroup\TaskGroupRestored;
use App\Models\ClientCompany;
use App\Models\Comment;
use App\Models\OwnerCompany;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskGroup;
use App\Models\User;
use App\Services\PermissionService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    resetPermissionServiceCache();

    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);

    $currencyId = DB::table('currencies')->insertGetId([
        'name' => 'US Dollar',
        'code' => 'USD',
        'symbol' => '$',
        'decimals' => 2,
    ]);

    OwnerCompany::create([
        'name' => 'Agency',
        'currency_id' => $currencyId,
    ]);

    $this->admin = User::factory()->create(['name' => 'Admin']);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);

    $acme = ClientCompany::create(['name' => 'Acme Corp']);
    $globexCompany = ClientCompany::create(['name' => 'Globex Inc']);

    $this->client = User::factory()->create(['name' => 'Alice']);
    $this->client->assignRole('client');
    $this->client->clientCompanies()->attach($acme);

    $this->project = Project::create([
        'client_company_id' => $acme->id,
        'name' => 'Acme Website',
    ]);

    $this->globex = Project::create([
        'client_company_id' => $globexCompany->id,
        'name' => 'Globex Secret Merger Portal',
    ]);

    $group = TaskGroup::create([
        'project_id' => $this->project->id,
        'name' => 'To Do',
        'order_column' => 1,
    ]);

    $this->publicTask = Task::create([
        'project_id' => $this->project->id,
        'group_id' => $group->id,
        'created_by_user_id' => $this->admin->id,
        'name' => 'Public homepage',
        'number' => 1,
        'description' => 'PUBLIC-DESC visible work',
        'hidden_from_clients' => false,
        'billable' => true,
        'order_column' => 1,
    ]);

    $this->hiddenTask = Task::create([
        'project_id' => $this->project->id,
        'group_id' => $group->id,
        'created_by_user_id' => $this->admin->id,
        'name' => 'Hidden estimate',
        'number' => 2,
        'description' => 'HIDDEN-DESC internal pricing',
        'estimation' => 12,
        'hidden_from_clients' => true,
        'billable' => true,
        'order_column' => 2,
    ]);

    $this->publicTask->comments()->create([
        'user_id' => $this->admin->id,
        'content' => 'PUBLIC-COMMENT hello',
    ]);

    $this->hiddenTask->comments()->create([
        'user_id' => $this->admin->id,
        'content' => 'HIDDEN-COMMENT staff only',
    ]);
});

afterEach(function () {
    resetPermissionServiceCache();
});

it('hides a client-hidden task from the board and direct task routes', function () {
    $this->actingAs($this->client)
        ->getJson(route('projects.tasks', $this->globex))
        ->assertForbidden();

    $this->actingAs($this->client)
        ->get(route('projects.tasks', $this->project))
        ->assertOk()
        ->assertSee('Public homepage')
        ->assertSee('PUBLIC-DESC visible work')
        ->assertDontSee('Hidden estimate')
        ->assertDontSee('HIDDEN-DESC internal pricing');

    $this->actingAs($this->client)
        ->get(route('projects.tasks.open', [$this->project, $this->hiddenTask]))
        ->assertNotFound()
        ->assertDontSee('HIDDEN-DESC internal pricing');

    $this->actingAs($this->client)
        ->get(route('projects.tasks.open', [$this->project, $this->publicTask]))
        ->assertOk()
        ->assertSee('PUBLIC-DESC visible work')
        ->assertDontSee('HIDDEN-DESC internal pricing');

    $this->actingAs($this->admin)
        ->get(route('projects.tasks.open', [$this->project, $this->hiddenTask]))
        ->assertOk()
        ->assertSee('HIDDEN-DESC internal pricing');
});

it('does not return comments, uploads, or timers for a task hidden from the client', function () {
    $this->actingAs($this->client)
        ->getJson(route('projects.tasks.comments', [$this->project, $this->hiddenTask]))
        ->assertNotFound()
        ->assertDontSee('HIDDEN-COMMENT staff only');

    $this->actingAs($this->client)
        ->getJson(route('projects.tasks.comments', [$this->project, $this->publicTask]))
        ->assertOk()
        ->assertJsonFragment(['content' => 'PUBLIC-COMMENT hello']);

    $this->actingAs($this->client)
        ->postJson(route('projects.tasks.comments.store', [$this->project, $this->hiddenTask]), [
            'content' => 'client reply',
        ])
        ->assertNotFound();

    expect(Comment::where('content', 'client reply')->exists())->toBeFalse();

    $this->actingAs($this->client)
        ->postJson(route('projects.tasks.attachments.upload', [$this->project, $this->hiddenTask]))
        ->assertNotFound();

    $this->actingAs($this->client)
        ->postJson(route('projects.tasks.time-logs.timer.start', [$this->project, $this->hiddenTask]))
        ->assertNotFound();
});

it('refuses the activity feed without the view activities permission', function () {
    $this->actingAs($this->client)
        ->getJson(route('my-work.activity.index'))
        ->assertForbidden();
});

it('limits activity for a client who can view activities to visible tasks and their projects', function () {
    $this->client->givePermissionTo('view activities');

    $this->actingAs($this->client)
        ->get(route('my-work.activity.index'))
        ->assertOk()
        ->assertSee('Public homepage')
        ->assertSee('PUBLIC-DESC visible work')
        ->assertSee('PUBLIC-COMMENT hello')
        ->assertSee('Acme Website')
        ->assertDontSee('Hidden estimate')
        ->assertDontSee('HIDDEN-DESC internal pricing')
        ->assertDontSee('HIDDEN-COMMENT staff only')
        ->assertDontSee('Globex Secret Merger Portal');
});

it('still shows hidden tasks and every project to someone who can view activities', function () {
    $this->actingAs($this->admin)
        ->get(route('my-work.activity.index'))
        ->assertOk()
        ->assertSee('HIDDEN-DESC internal pricing')
        ->assertSee('HIDDEN-COMMENT staff only')
        ->assertSee('Globex Secret Merger Portal');
});

it('omits hidden tasks from the client dashboard and invoice task list', function () {
    $this->actingAs($this->admin);
    $this->hiddenTask->update([
        'assigned_to_user_id' => $this->client->id,
        'assigned_at' => now(),
        'due_on' => now()->subDay()->toDateString(),
    ]);

    $this->actingAs($this->client)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Hidden estimate')
        ->assertDontSee('HIDDEN-COMMENT staff only')
        ->assertInertia(fn (Assert $page) => $page
            ->where('projects.0.all_tasks_count', 1)
            ->has('overdueTasks', 0)
            ->has('recentlyAssignedTasks', 0)
            ->has('recentComments', 0));

    $this->actingAs($this->admin);
    $this->publicTask->update(['completed_at' => now()]);
    $this->hiddenTask->update(['completed_at' => now()]);

    $this->actingAs($this->client)
        ->getJson(route('invoices.tasks', ['projectIds' => [$this->project->id]]))
        ->assertOk()
        ->assertJsonFragment(['name' => 'Public homepage'])
        ->assertJsonMissing(['name' => 'Hidden estimate']);
});

it('does not notify a client about a task hidden from clients', function () {
    $this->hiddenTask->subscribedUsers()->attach($this->client);
    $this->publicTask->subscribedUsers()->attach($this->client);

    $this->actingAs($this->admin);
    TaskCreated::dispatch($this->hiddenTask);

    expect($this->client->fresh()->notifications)->toHaveCount(0);

    TaskCreated::dispatch($this->publicTask);

    expect($this->client->fresh()->notifications)->toHaveCount(1);
});

it('broadcasts hidden task details on the staff channel only', function () {
    $hiddenCreated = new TaskCreated($this->hiddenTask);
    $publicCreated = new TaskCreated($this->publicTask);
    $visibilityChange = new TaskUpdated($this->publicTask, 'hidden_from_clients');

    expect($hiddenCreated->broadcastOn()[0]->name)->toBe('private-App.Models.Project.'.$this->project->id.'.internal')
        ->and($publicCreated->broadcastOn()[0]->name)->toBe('private-App.Models.Project.'.$this->project->id)
        ->and($visibilityChange->broadcastOn()[0]->name)->toBe('private-App.Models.Project.'.$this->project->id.'.internal');

    $channels = Broadcast::connection()->getChannels();

    expect($channels)->not->toHaveKey('App.Models.Task.{id}')
        ->and($channels['App.Models.Project.{id}.internal']($this->client, $this->project->id))->toBeFalse()
        ->and($channels['App.Models.Project.{id}.internal']($this->admin, $this->project->id))->toBeTrue()
        ->and($channels['App.Models.Project.{id}']($this->client, $this->project->id))->toBeTrue()
        ->and($channels['App.Models.Project.{id}']($this->client, $this->globex->id))->toBeFalse();
});

it('caches accessible projects separately for each user', function () {
    $clientProjects = PermissionService::projectsThatUserCanAccess($this->client);
    $adminProjects = PermissionService::projectsThatUserCanAccess($this->admin);

    expect($clientProjects->pluck('id'))->not->toContain($this->globex->id)
        ->and($adminProjects->pluck('id'))->toContain($this->globex->id);
});

it('does not list members of a project the user cannot access', function () {
    $this->actingAs($this->client)
        ->getJson(route('dropdown.values', [
            'mentionProjectUsers' => 1,
            'projectId' => $this->globex->id,
        ]))
        ->assertForbidden();

    $this->actingAs($this->client)
        ->getJson(route('dropdown.values', [
            'mentionProjectUsers' => 1,
            'projectId' => $this->project->id,
        ]))
        ->assertOk()
        ->assertJsonFragment(['Alice']);
});

it('does not reorder or move tasks from another project', function () {
    Event::fake([TaskOrderChanged::class, TaskGroupChanged::class]);

    $foreignGroup = TaskGroup::create([
        'project_id' => $this->globex->id,
        'name' => 'Secret',
        'order_column' => 1,
    ]);

    $foreignTask = Task::create([
        'project_id' => $this->globex->id,
        'group_id' => $foreignGroup->id,
        'created_by_user_id' => $this->admin->id,
        'name' => 'Foreign task',
        'number' => 1,
        'hidden_from_clients' => true,
        'billable' => true,
        'order_column' => 4,
    ]);

    $foreignOrder = $foreignTask->fresh()->order_column;

    $groupId = $this->publicTask->group_id;

    $this->actingAs($this->admin)
        ->postJson(route('projects.tasks.reorder', $this->project), [
            'ids' => [$this->hiddenTask->id, $this->publicTask->id],
            'group_id' => $groupId,
            'task_id' => $this->hiddenTask->id,
        ])
        ->assertOk();

    expect($this->hiddenTask->fresh()->order_column)->toBe(1)
        ->and($this->publicTask->fresh()->order_column)->toBe(2);

    Event::assertDispatched(TaskOrderChanged::class, function (TaskOrderChanged $event) use ($groupId) {
        return $event->groupId === $groupId
            && $event->taskId === $this->hiddenTask->id
            && $event->taskIds === [$this->hiddenTask->id, $this->publicTask->id];
    });

    $this->actingAs($this->admin)
        ->postJson(route('projects.tasks.reorder', $this->project), [
            'ids' => [$foreignTask->id, $this->publicTask->id],
            'group_id' => $groupId,
            'task_id' => $foreignTask->id,
        ])
        ->assertOk();

    expect($foreignTask->fresh()->order_column)->toBe($foreignOrder);

    Event::assertDispatched(TaskOrderChanged::class, function (TaskOrderChanged $event) use ($foreignTask) {
        return $event->taskIds === [$this->publicTask->id]
            && ! in_array($foreignTask->id, $event->taskIds, true)
            && $event->taskId === 0;
    });

    $this->actingAs($this->admin)
        ->postJson(route('projects.tasks.move', $this->project), [
            'ids' => [$foreignTask->id, $this->publicTask->id],
            'to_group_id' => $foreignGroup->id,
            'from_group_id' => $this->publicTask->group_id,
            'from_index' => 0,
            'to_index' => 0,
        ])
        ->assertNotFound();

    expect($foreignTask->fresh()->group_id)->toBe($foreignGroup->id)
        ->and($this->publicTask->fresh()->group_id)->toBe($groupId);

    $destinationGroup = TaskGroup::create([
        'project_id' => $this->project->id,
        'name' => 'Doing',
        'order_column' => 2,
    ]);

    $this->actingAs($this->admin)
        ->postJson(route('projects.tasks.move', $this->project), [
            'ids' => [$this->publicTask->id, $this->hiddenTask->id],
            'from_group_id' => $groupId,
            'to_group_id' => $destinationGroup->id,
            'task_id' => $this->publicTask->id,
        ])
        ->assertOk();

    Event::assertDispatched(TaskGroupChanged::class, function (TaskGroupChanged $event) use ($destinationGroup, $groupId) {
        return $event->fromGroupId === $groupId
            && $event->toGroupId === $destinationGroup->id
            && $event->taskId === $this->publicTask->id
            && $event->taskIds === [$this->publicTask->id, $this->hiddenTask->id];
    });

    expect($this->publicTask->fresh()->group_id)->toBe($destinationGroup->id);
});

it('broadcasts comments and deletes for hidden tasks on the staff channel only', function () {
    $hiddenComment = new CommentCreated($this->hiddenTask->comments()->first());
    $publicComment = new CommentCreated($this->publicTask->comments()->first());

    expect($hiddenComment->broadcastOn()[0]->name)->toBe('private-App.Models.Project.'.$this->project->id.'.internal')
        ->and($publicComment->broadcastOn()[0]->name)->toBe('private-App.Models.Project.'.$this->project->id)
        ->and($hiddenComment->comment->relationLoaded('task'))->toBeFalse()
        ->and($hiddenComment->comment->content)->toBe('HIDDEN-COMMENT staff only');

    $this->hiddenTask->archive();
    $hiddenDeleted = new TaskDeleted($this->hiddenTask);
    $publicDeleted = new TaskDeleted($this->publicTask);

    expect($hiddenDeleted->broadcastOn()[0]->name)->toBe('private-App.Models.Project.'.$this->project->id.'.internal')
        ->and($hiddenDeleted->broadcastWith())->toBe(['taskId' => $this->hiddenTask->id])
        ->and(json_encode($hiddenDeleted->broadcastWith()))->not->toContain('HIDDEN-DESC')
        ->and($publicDeleted->broadcastOn()[0]->name)->toBe('private-App.Models.Project.'.$this->project->id);
});

it('sends restored group tasks to clients without hidden tasks', function () {
    $group = TaskGroup::findOrFail($this->publicTask->group_id);
    $group->archive();

    Event::fake([TaskGroupRestored::class]);

    $this->actingAs($this->admin)
        ->post(route('projects.task-groups.restore', [$this->project, $group->id]))
        ->assertRedirect();

    $events = Event::dispatched(TaskGroupRestored::class)
        ->map(fn (array $arguments) => $arguments[0]);

    $publicEvent = $events->first(
        fn (TaskGroupRestored $event) => $event->broadcastOn()[0]->name === 'private-App.Models.Project.'.$this->project->id
    );
    $internalEvent = $events->first(
        fn (TaskGroupRestored $event) => $event->broadcastOn()[0]->name === 'private-App.Models.Project.'.$this->project->id.'.internal'
    );

    expect($publicEvent)->not->toBeNull()
        ->and(collect($publicEvent->tasks)->pluck('id')->all())->toBe([$this->publicTask->id])
        ->and(json_encode($publicEvent->tasks))->not->toContain('Hidden estimate')
        ->and($internalEvent)->not->toBeNull()
        ->and(collect($internalEvent->tasks)->pluck('id')->all())->toContain($this->hiddenTask->id)
        ->and(collect($internalEvent->tasks)->pluck('id')->all())->toContain($this->publicTask->id);
});

it('tells connected clients when a task is hidden or shown without sending hidden details', function () {
    $shown = new TaskVisibilityChanged($this->publicTask);

    expect($shown->broadcastOn()[0]->name)->toBe('private-App.Models.Project.'.$this->project->id)
        ->and($shown->broadcastWith()['hidden'])->toBeFalse()
        ->and($shown->broadcastWith()['task']->description)->toBe('PUBLIC-DESC visible work');

    $hidden = new TaskVisibilityChanged($this->hiddenTask);

    expect($hidden->broadcastOn()[0]->name)->toBe('private-App.Models.Project.'.$this->project->id)
        ->and($hidden->broadcastWith())->toBe([
            'hidden' => true,
            'taskId' => $this->hiddenTask->id,
        ])
        ->and(json_encode($hidden->broadcastWith()))->not->toContain('HIDDEN-DESC');

    Event::fake([TaskVisibilityChanged::class]);

    $this->actingAs($this->admin);
    (new UpdateTask)->update($this->publicTask, ['hidden_from_clients' => true]);

    Event::assertDispatched(TaskVisibilityChanged::class, function (TaskVisibilityChanged $event) {
        return $event->broadcastWith() === [
            'hidden' => true,
            'taskId' => $this->publicTask->id,
        ];
    });
});

function resetPermissionServiceCache(): void
{
    (new ReflectionProperty(PermissionService::class, 'projectsThatUserCanAccess'))->setValue(null, []);
    (new ReflectionProperty(PermissionService::class, 'usersWithAccessToProject'))->setValue(null, []);
}
