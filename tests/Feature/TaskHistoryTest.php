<?php

use App\Models\ClientCompany;
use App\Models\Label;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\TaskGroup;
use App\Models\User;
use App\Services\PermissionService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OwenIt\Auditing\Models\Audit;

uses(RefreshDatabase::class);

beforeEach(function () {
    // The Auditable trait registers its observer when the model boots.
    // Console auditing is checked when the event fires, so this has to be
    // set before the task is created.
    config(['audit.console' => true]);
    resetTaskHistoryPermissionCache();

    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['name' => 'Admin']);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);

    $company = ClientCompany::create(['name' => 'Acme']);

    $this->client = User::factory()->create(['name' => 'Alice']);
    $this->client->assignRole('client');
    $this->client->clientCompanies()->attach($company);

    $this->project = Project::create([
        'client_company_id' => $company->id,
        'name' => 'Acme Website',
    ]);

    $this->group = TaskGroup::create([
        'project_id' => $this->project->id,
        'name' => 'To Do',
        'order_column' => 1,
    ]);

    $this->task = Task::create([
        'project_id' => $this->project->id,
        'group_id' => $this->group->id,
        'created_by_user_id' => $this->admin->id,
        'name' => 'Original title',
        'number' => 1,
        'hidden_from_clients' => false,
        'billable' => true,
        'order_column' => 1,
    ]);

    $this->hiddenTask = Task::create([
        'project_id' => $this->project->id,
        'group_id' => $this->group->id,
        'created_by_user_id' => $this->admin->id,
        'name' => 'Hidden estimate',
        'number' => 2,
        'hidden_from_clients' => true,
        'billable' => true,
        'order_column' => 2,
    ]);
});

afterEach(function () {
    $this->travelBack();
    resetTaskHistoryPermissionCache();
});

it('forbids users without access to the project', function () {
    $outsider = User::factory()->create();
    $outsider->assignRole('developer');

    $this->actingAs($outsider)
        ->getJson(route('projects.tasks.history', [$this->project, $this->task]))
        ->assertForbidden();
});

it('returns not found when the task belongs to another project', function () {
    $other = Project::create([
        'client_company_id' => $this->project->client_company_id,
        'name' => 'Other',
    ]);

    $this->getJson(route('projects.tasks.history', [$other, $this->task]))
        ->assertNotFound();
});

it('returns not found when a client opens a task hidden from clients', function () {
    $this->actingAs($this->client)
        ->getJson(route('projects.tasks.history', [$this->project, $this->hiddenTask]))
        ->assertNotFound();
});

it('hides hidden-from-clients changes from clients', function () {
    $this->task->update(['hidden_from_clients' => true]);

    $this->travel(11)->minutes();

    $this->task->update([
        'hidden_from_clients' => false,
        'name' => 'Renamed public task',
    ]);

    $adminFields = collect(taskHistory($this->project, $this->task)['data'])->pluck('field');

    expect($adminFields)->toContain('hidden_from_clients')
        ->and($adminFields)->toContain('name');

    $clientFields = collect(
        $this->actingAs($this->client)->getJson(route('projects.tasks.history', [$this->project, $this->task]))
            ->assertOk()
            ->json('data'),
    )->pluck('field');

    expect($clientFields)->not->toContain('hidden_from_clients')
        ->and($clientFields)->toContain('name');
});

it('hides price changes from users who cannot view pricing', function () {
    $role = Role::create(['name' => 'history viewer', 'guard_name' => 'web']);
    $role->givePermissionTo('view tasks');

    $viewer = User::factory()->create(['name' => 'Viewer']);
    $viewer->assignRole($role);
    $this->project->users()->attach($viewer);

    $this->task->update([
        'name' => 'Scoped task',
        'pricing_type' => 'fixed',
        'fixed_price' => 12500,
        'billable' => false,
    ]);

    $adminFields = collect(taskHistory($this->project, $this->task)['data'])->pluck('field');

    expect($adminFields)->toContain('name')
        ->and($adminFields)->toContain('pricing_type')
        ->and($adminFields)->toContain('fixed_price')
        ->and($adminFields)->toContain('billable');

    $viewerFields = collect(
        $this->actingAs($viewer)
            ->getJson(route('projects.tasks.history', [$this->project, $this->task]))
            ->assertOk()
            ->json('data'),
    )->pluck('field');

    expect($viewerFields)->toContain('name')
        ->and($viewerFields)->not->toContain('pricing_type')
        ->and($viewerFields)->not->toContain('fixed_price')
        ->and($viewerFields)->not->toContain('billable');
});

it('records label and subscriber changes', function () {
    $bug = Label::create(['name' => 'Bug', 'color' => '#e03131']);
    $feature = Label::create(['name' => 'Feature', 'color' => '#1971c2']);

    $this->putJson(route('projects.tasks.update', [$this->project, $this->task]), [
        'labels' => [$bug->id],
    ])->assertOk();

    $this->putJson(route('projects.tasks.update', [$this->project, $this->task]), [
        'labels' => [$bug->id, $feature->id],
    ])->assertOk();

    $this->putJson(route('projects.tasks.update', [$this->project, $this->task]), [
        'subscribed_users' => [$this->client->id],
    ])->assertOk();

    $this->client->update(['name' => 'Alice Updated']);

    $entries = collect(taskHistory($this->project, $this->task)['data']);
    $labels = $entries->where('field', 'labels')->values();
    $subscribers = $entries->where('field', 'subscribed_users')->values();

    expect($labels)->toHaveCount(1)
        ->and(collect($labels[0]['new'])->pluck('name')->sort()->values()->all())->toBe(['Bug', 'Feature'])
        ->and($labels[0]['old'])->toBe([])
        ->and(collect($labels[0]['new'])->firstWhere('name', 'Bug')['color'])->toBe('#e03131')
        ->and($subscribers)->toHaveCount(1)
        ->and($subscribers[0]['old'])->toBe([])
        ->and($subscribers[0]['new'][0]['name'])->toBe('Alice Updated');
});

it('records a task moved between groups', function () {
    $doing = TaskGroup::create([
        'project_id' => $this->project->id,
        'name' => 'Doing',
        'order_column' => 2,
    ]);

    $this->postJson(route('projects.tasks.move', $this->project), [
        'ids' => [$this->task->id],
        'from_group_id' => $this->group->id,
        'to_group_id' => $doing->id,
    ])->assertOk();

    expect($this->task->fresh()->group_id)->toBe($doing->id);

    $move = collect(taskHistory($this->project, $this->task)['data'])->firstWhere('field', 'group_id');

    expect($move)->not->toBeNull()
        ->and($move['old']['name'])->toBe('To Do')
        ->and($move['new']['name'])->toBe('Doing');
});

it('does not record a reorder', function () {
    $before = $this->task->audits()->count();

    $this->postJson(route('projects.tasks.reorder', $this->project), [
        'ids' => [$this->task->id],
        'group_id' => $this->group->id,
    ])->assertOk();

    expect($this->task->audits()->count())->toBe($before);

    $fields = collect(taskHistory($this->project, $this->task)['data'])->pluck('field');

    expect($fields)->not->toContain('order_column');
});

it('records archiving and restoring a task', function () {
    $this->delete(route('projects.tasks.destroy', [$this->project, $this->task]))
        ->assertRedirect();

    $this->post(route('projects.tasks.restore', [$this->project, $this->task]))
        ->assertRedirect();

    $types = collect(taskHistory($this->project, $this->task)['data'])->pluck('type');

    expect($types)->toContain('created')
        ->and($types)->toContain('archived')
        ->and($types)->toContain('restored');
});

it('merges consecutive edits to the same field within ten minutes', function () {
    $this->task->update(['name' => 'First draft']);
    $this->task->update(['name' => 'Second draft']);
    $this->task->update(['name' => 'Final draft']);

    $names = collect(taskHistory($this->project, $this->task)['data'])
        ->where('field', 'name')
        ->values();

    expect($names)->toHaveCount(1)
        ->and($names[0]['old'])->toBe('Original title')
        ->and($names[0]['new'])->toBe('Final draft')
        ->and($names[0]['user']['id'])->toBe($this->admin->id)
        ->and($names[0]['user']['name'])->toBe('Admin');
});

it('paginates history newest first', function () {
    foreach (range(1, 31) as $index) {
        $createdAt = now()->subMinutes(($index + 1) * 11);

        Audit::create([
            'user_type' => User::class,
            'user_id' => $this->admin->id,
            'event' => 'updated',
            'auditable_type' => Task::class,
            'auditable_id' => $this->task->id,
            'old_values' => ['name' => 'Name '.$index],
            'new_values' => ['name' => 'Name '.($index + 1)],
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    $firstPage = taskHistory($this->project, $this->task);

    expect($firstPage['has_more'])->toBeTrue()
        ->and($firstPage['data'][0]['type'])->toBe('created')
        ->and(collect($firstPage['data'])->where('field', 'name'))->toHaveCount(29);

    $secondPage = $this->getJson(route('projects.tasks.history', [
        'project' => $this->project,
        'task' => $this->task,
        'page' => 2,
    ]))->assertOk()->json();

    expect($secondPage['has_more'])->toBeFalse()
        ->and(collect($secondPage['data'])->where('field', 'name'))->toHaveCount(2);
});

function taskHistory(Project $project, Task $task): array
{
    return test()
        ->getJson(route('projects.tasks.history', [$project, $task]))
        ->assertOk()
        ->json();
}

function resetTaskHistoryPermissionCache(): void
{
    (new ReflectionProperty(PermissionService::class, 'projectsThatUserCanAccess'))->setValue(null, []);
    (new ReflectionProperty(PermissionService::class, 'usersWithAccessToProject'))->setValue(null, []);
}
