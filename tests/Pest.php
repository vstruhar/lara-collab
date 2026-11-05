<?php

use App\Models\ClientCompany;
use App\Models\OwnerCompany;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskGroup;
use App\Models\User;
use App\Services\PermissionService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(
    TestCase::class,
    // Illuminate\Foundation\Testing\RefreshDatabase::class,
)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function resetPermissionServiceCache(): void
{
    (new ReflectionProperty(PermissionService::class, 'projectsThatUserCanAccess'))->setValue(null, []);
    (new ReflectionProperty(PermissionService::class, 'usersWithAccessToProject'))->setValue(null, []);
}

function seedAccessControl(): void
{
    test()->seed(RoleSeeder::class);
    test()->seed(PermissionSeeder::class);
}

function createOwnerCompany(): OwnerCompany
{
    $currencyId = DB::table('currencies')->insertGetId([
        'name' => 'US Dollar',
        'code' => 'USD',
        'symbol' => '$',
        'decimals' => 2,
    ]);

    return OwnerCompany::create([
        'name' => 'Agency',
        'currency_id' => $currencyId,
    ]);
}

function createAdmin(): User
{
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    return $admin;
}

/**
 * @return array{company: ClientCompany, project: Project, group: TaskGroup, task: Task}
 */
function createProjectWithTask(?User $creator = null): array
{
    $company = ClientCompany::create(['name' => 'Acme Corp']);

    $project = Project::create([
        'client_company_id' => $company->id,
        'name' => 'Acme Website',
    ]);

    $group = TaskGroup::create([
        'project_id' => $project->id,
        'name' => 'To Do',
    ]);

    $task = Task::create([
        'project_id' => $project->id,
        'group_id' => $group->id,
        'created_by_user_id' => $creator?->id ?? auth()->id(),
        'name' => 'Homepage',
        'number' => 1,
        'hidden_from_clients' => false,
        'billable' => true,
    ]);

    return compact('company', 'project', 'group', 'task');
}
