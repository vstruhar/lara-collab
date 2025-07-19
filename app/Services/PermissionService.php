<?php

namespace App\Services;

use App\Models\ClientCompany;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;

class PermissionService
{
    public static $permissionsByRole = [
        'admin' => [
            'User' => ['view users', 'view user rate', 'create user', 'edit user', 'archive user', 'restore user'],
            'Label' => ['view labels', 'create label', 'edit label', 'archive label', 'restore label'],
            'Role' => ['view roles', 'create role', 'edit role', 'archive role', 'restore role'],
            'Owner Company' => ['view owner company', 'edit owner company'],
            'Client User' => ['view client users', 'create client user', 'edit client user', 'archive client user', 'restore client user'],
            'Client Company' => ['view client companies', 'create client company', 'edit client company', 'archive client company', 'restore client company'],
            'Project' => ['view projects', 'view project', 'create project', 'edit project', 'archive project', 'restore project', 'edit project user access'],
            'TaskGroups' => ['create task group', 'edit task group', 'archive task group', 'restore task group', 'reorder task group'],
            'Tasks' => [
                'view tasks', 'create task', 'edit task', 'archive task', 'restore task', 'reorder task', 'complete task', 'add time log', 'delete time log',
                'view time logs', 'view comments',
            ],
            'Invoices' => ['view invoices', 'create invoice', 'edit invoice', 'archive invoice', 'restore invoice', 'change invoice status', 'download invoice', 'print invoice'],
            'Reports' => ['view logged time sum report', 'view daily logged time report', 'view fixed price sum report'],
            'Activities' => ['view activities'],
        ],
        'manager' => [
            'User' => ['view users'],
            'Project' => ['view projects', 'view project', 'create project', 'edit project', 'archive project', 'restore project', 'edit project user access'],
            'TaskGroups' => ['create task group', 'edit task group', 'archive task group', 'restore task group', 'reorder task group'],
            'Tasks' => [
                'view tasks', 'create task', 'edit task', 'archive task', 'restore task', 'reorder task', 'complete task', 'add time log', 'delete time log',
                'view time logs', 'view comments',
            ],
            'Reports' => ['view logged time sum report', 'view daily logged time report', 'view fixed price sum report'],
        ],
        'developer' => [
            'Project' => ['view projects', 'view project'],
            'Tasks' => [
                'view tasks', 'create task', 'edit task', 'restore task', 'reorder task', 'complete task', 'add time log', 'delete time log',
                'view time logs', 'view comments',
            ],
        ],
        'qa engineer' => [
            'Project' => ['view projects', 'view project'],
            'Tasks' => [
                'view tasks', 'create task', 'edit task', 'add time log', 'delete time log', 'view time logs', 'view comments',
            ],
        ],
        'designer' => [
            'Project' => ['view projects', 'view project'],
            'Tasks' => [
                'view tasks', 'create task', 'edit task', 'restore task', 'reorder task', 'complete task', 'add time log', 'delete time log',
                'view time logs', 'view comments',
            ],
        ],
        'client' => [
            'Project' => ['view projects', 'view project'],
            'Tasks' => [
                'view tasks', 'create task', 'view time logs', 'view comments',
            ],
        ],
    ];

    public static function allPermissionsGrouped(): array
    {
        return self::$permissionsByRole['admin'];
    }

    private static $usersWithAccessToProject = [];

    public static function usersWithAccessToProject($project): Collection
    {
        if (isset(self::$usersWithAccessToProject[$project->id])) {
            return self::$usersWithAccessToProject[$project->id];
        }

        $admins = User::role('admin')
            ->with('roles:id,name')
            ->get(['id', 'name', 'avatar'])
            ->map(fn ($user) => [...$user->toArray(), 'reason' => 'admin']);

        $owners = $project
            ->clientCompany
            ->clients
            ->load('roles:id,name')
            ->map(fn ($user) => [...$user->toArray(), 'reason' => 'company owner']);

        $givenAccess = $project
            ->users
            ->load('roles:id,name')
            ->map(fn ($user) => [...$user->toArray(), 'reason' => 'given access']);

        return self::$usersWithAccessToProject[$project->id] = collect([
            ...$admins,
            ...$owners,
            ...$givenAccess,
        ])
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    private static $projectsThatUserCanAccess = null;

    public static function projectsThatUserCanAccess(User $user): Collection
    {
        if (self::$projectsThatUserCanAccess !== null) {
            return self::$projectsThatUserCanAccess;
        }
        if ($user->hasRole('admin')) {
            return Project::all();
        }
        $projects = collect($user->projects->toArray());
        $user->load('clientCompanies.projects');

        return self::$projectsThatUserCanAccess = $projects
            ->merge(
                $user
                    ->clientCompanies
                    ->map(fn (ClientCompany $company) => $company->projects->toArray())
                    ->collapse()
            )
            ->unique('id')
            ->sortBy('name')
            ->values();
    }
}
