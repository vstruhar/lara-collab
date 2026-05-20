<?php

use App\Actions\Task\CreateTask;
use App\Actions\Task\UpdateTask;
use App\Models\ClientCompany;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskGroup;
use App\Models\TaskPriority;
use App\Models\User;
use Database\Seeders\CountrySeeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TaskPrioritySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed necessary data
    $this->seed(CountrySeeder::class);
    $this->seed(CurrencySeeder::class);
    $this->seed(TaskPrioritySeeder::class);
    $this->seed(RoleSeeder::class);
    $this->seed(PermissionSeeder::class);

    // Create a user with necessary permissions
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Assign admin role to user for testing
    $this->user->assignRole('admin');

    // Create a client company and project
    $clientCompany = ClientCompany::factory()->create();

    $this->project = Project::create([
        'client_company_id' => $clientCompany->id,
        'name' => 'Test Project',
        'hourly_rate' => 5000,
        'default_pricing_type' => 'hourly',
    ]);

    $this->taskGroup = TaskGroup::create([
        'project_id' => $this->project->id,
        'name' => 'To Do',
        'color' => 'blue',
    ]);
});

it('can create a task with a priority', function () {
    $priority = TaskPriority::where('label', 'High')->first();

    $taskData = [
        'name' => 'Task with priority',
        'group_id' => $this->taskGroup->id,
        'assigned_to_user_id' => null,
        'description' => 'Test description',
        'due_on' => null,
        'estimation' => null,
        'priority_id' => $priority->id,
        'pricing_type' => 'hourly',
        'fixed_price' => null,
        'hidden_from_clients' => false,
        'billable' => true,
    ];

    $task = (new CreateTask)->create($this->project, $taskData);

    expect($task)->toBeInstanceOf(Task::class)
        ->and($task->priority_id)->toBe($priority->id);

    // Test manual loading
    $manualPriority = TaskPriority::find($task->priority_id);
    expect($manualPriority)->not->toBeNull()
        ->and($manualPriority->label)->toBe('High');

});

it('can create a task without a priority', function () {
    $taskData = [
        'name' => 'Task without priority',
        'group_id' => $this->taskGroup->id,
        'assigned_to_user_id' => null,
        'description' => 'Test description',
        'due_on' => null,
        'estimation' => null,
        'priority_id' => null,
        'pricing_type' => 'hourly',
        'fixed_price' => null,
        'hidden_from_clients' => false,
        'billable' => true,
    ];

    $task = (new CreateTask)->create($this->project, $taskData);

    expect($task)->toBeInstanceOf(Task::class)
        ->and($task->priority_id)->toBeNull()
        ->and($task->priority)->toBeNull();
});

it('can create a task with empty string priority_id', function () {
    $taskData = [
        'name' => 'Task with empty priority',
        'group_id' => $this->taskGroup->id,
        'assigned_to_user_id' => null,
        'description' => 'Test description',
        'due_on' => null,
        'estimation' => null,
        'priority_id' => '',
        'pricing_type' => 'hourly',
        'fixed_price' => null,
        'hidden_from_clients' => false,
        'billable' => true,
    ];

    $task = (new CreateTask)->create($this->project, $taskData);

    expect($task)->toBeInstanceOf(Task::class)
        ->and($task->priority_id)->toBeNull()
        ->and($task->priority)->toBeNull();
});

it('can update a task priority', function () {
    // Create task without priority
    $task = Task::create([
        'project_id' => $this->project->id,
        'group_id' => $this->taskGroup->id,
        'created_by_user_id' => $this->user->id,
        'name' => 'Test Task',
        'number' => 1,
        'pricing_type' => 'hourly',
        'hidden_from_clients' => false,
        'billable' => true,
    ]);

    expect($task->priority_id)->toBeNull();

    // Update with priority
    $priority = TaskPriority::where('label', 'High')->first();
    (new UpdateTask)->update($task, ['priority_id' => $priority->id]);

    // Reload the task and verify priority_id was saved
    $task->refresh();
    expect($task->priority_id)->toBe($priority->id);

    // Verify the priority can be manually fetched
    $fetchedPriority = TaskPriority::find($task->priority_id);
    expect($fetchedPriority->label)->toBe('High');
});

it('can change a task priority to a different priority', function () {
    $lowPriority = TaskPriority::where('label', 'Low')->first();
    $highPriority = TaskPriority::where('label', 'High')->first();

    // Create task with low priority
    $task = Task::create([
        'project_id' => $this->project->id,
        'group_id' => $this->taskGroup->id,
        'created_by_user_id' => $this->user->id,
        'name' => 'Test Task',
        'number' => 1,
        'priority_id' => $lowPriority->id,
        'pricing_type' => 'hourly',
        'hidden_from_clients' => false,
        'billable' => true,
    ]);

    expect($task->priority_id)->toBe($lowPriority->id);

    // Update to high priority
    (new UpdateTask)->update($task, ['priority_id' => $highPriority->id]);

    // Reload and verify priority_id was updated
    $task->refresh();
    expect($task->priority_id)->toBe($highPriority->id);

    // Verify we can fetch the correct priority
    $fetchedPriority = TaskPriority::find($task->priority_id);
    expect($fetchedPriority->label)->toBe('High');
});

it('can clear a task priority', function () {
    $priority = TaskPriority::where('label', 'High')->first();

    // Create task with priority
    $task = Task::create([
        'project_id' => $this->project->id,
        'group_id' => $this->taskGroup->id,
        'created_by_user_id' => $this->user->id,
        'name' => 'Test Task',
        'number' => 1,
        'priority_id' => $priority->id,
        'pricing_type' => 'hourly',
        'hidden_from_clients' => false,
        'billable' => true,
    ]);

    expect($task->priority_id)->toBe($priority->id);

    // Clear priority
    (new UpdateTask)->update($task, ['priority_id' => null]);

    $task->refresh();
    $task->load('priority');

    expect($task->priority_id)->toBeNull()
        ->and($task->priority)->toBeNull();
});

it('saves priority_id correctly for future relationship loading', function () {
    $priority = TaskPriority::where('label', 'Medium')->first();

    $task = Task::create([
        'project_id' => $this->project->id,
        'group_id' => $this->taskGroup->id,
        'created_by_user_id' => $this->user->id,
        'name' => 'Test Task',
        'number' => 1,
        'priority_id' => $priority->id,
        'pricing_type' => 'hourly',
        'hidden_from_clients' => false,
        'billable' => true,
    ]);

    // Verify the priority_id is saved correctly
    expect($task->priority_id)->toBe($priority->id);

    // Verify we can manually fetch the priority using the saved ID
    $fetchedPriority = TaskPriority::find($task->priority_id);
    expect($fetchedPriority)->not->toBeNull()
        ->and($fetchedPriority->label)->toBe('Medium');
});

it('can create a task via HTTP request with priority', function () {
    $priority = TaskPriority::where('label', 'High')->first();

    $response = $this->post(route('projects.tasks.store', $this->project), [
        'name' => 'New Task with Priority',
        'group_id' => $this->taskGroup->id,
        'assigned_to_user_id' => null,
        'description' => 'Task description',
        'due_on' => null,
        'estimation' => null,
        'priority_id' => $priority->id,
        'pricing_type' => 'hourly',
        'fixed_price' => null,
        'hidden_from_clients' => false,
        'billable' => true,
        'subscribed_users' => [],
        'labels' => [],
        'attachments' => [],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('flash');

    $task = Task::where('name', 'New Task with Priority')->first();

    expect($task)->not->toBeNull()
        ->and($task->priority_id)->toBe($priority->id);
});

it('can create a task via HTTP request without priority', function () {
    $response = $this->post(route('projects.tasks.store', $this->project), [
        'name' => 'New Task without Priority',
        'group_id' => $this->taskGroup->id,
        'assigned_to_user_id' => null,
        'description' => 'Task description',
        'due_on' => null,
        'estimation' => null,
        'priority_id' => null,
        'pricing_type' => 'hourly',
        'fixed_price' => null,
        'hidden_from_clients' => false,
        'billable' => true,
        'subscribed_users' => [],
        'labels' => [],
        'attachments' => [],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('flash');

    $task = Task::where('name', 'New Task without Priority')->first();

    expect($task)->not->toBeNull()
        ->and($task->priority_id)->toBeNull();
});

it('can update a task priority via HTTP request', function () {
    $task = Task::create([
        'project_id' => $this->project->id,
        'group_id' => $this->taskGroup->id,
        'created_by_user_id' => $this->user->id,
        'name' => 'Test Task',
        'number' => 1,
        'pricing_type' => 'hourly',
        'hidden_from_clients' => false,
        'billable' => true,
    ]);

    $priority = TaskPriority::where('label', 'High')->first();

    $response = $this->put(route('projects.tasks.update', [$this->project, $task]), [
        'priority_id' => $priority->id,
    ]);

    $response->assertOk();

    $task->refresh();

    expect($task->priority_id)->toBe($priority->id);
});

it('validates that priority_id exists in task_priorities table', function () {
    $response = $this->post(route('projects.tasks.store', $this->project), [
        'name' => 'Task with invalid priority',
        'group_id' => $this->taskGroup->id,
        'description' => 'Task description',
        'priority_id' => 9999, // Non-existent priority
        'pricing_type' => 'hourly',
        'hidden_from_clients' => false,
        'billable' => true,
        'subscribed_users' => [],
        'labels' => [],
        'attachments' => [],
    ]);

    $response->assertSessionHasErrors('priority_id');
});
