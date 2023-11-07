<?php

namespace App\Services;

use App\Models\User;

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
            'Project' => ['view projects', 'view project', 'create project', 'edit project', 'archive project', 'restore project', 'assign users to project'],
            'Tasks' => ['view tasks', 'create task', 'edit task', 'archive task', 'restore task', 'reorder task'],
            'TaskGroups' => ['create task group', 'edit task group', 'archive task group', 'restore task group', 'reorder task group'],
            'Invoices' => ['view invoices'],
            'Reports' => ['view reports'],
            'Activities' => ['view activities'],
        ],
        'manager' => [
            'User' => ['view users'],
            'Project' => ['view projects', 'view project', 'create project', 'edit project', 'archive project', 'restore project', 'assign users to project'],
            'Tasks' => ['view tasks', 'create task', 'edit task', 'archive task', 'restore task', 'reorder task'],
            'TaskGroups' => ['create task group', 'edit task group', 'archive task group', 'restore task group', 'reorder task group'],
            'Reports' => ['view reports'],
        ],
        'developer' => [
            'Project' => ['view projects', 'view project'],
            'Tasks' => ['view tasks', 'create task', 'edit task', 'reorder task'],
        ],
        'designer' => [
            'Project' => ['view projects', 'view project'],
            'Tasks' => ['view tasks', 'create task', 'edit task', 'reorder task'],
        ],
        'client' => [
            'Project' => ['view projects', 'view project'],
            'Tasks' => ['view tasks', 'create task', 'edit task', 'reorder task'],
        ],
    ];

    public static function allPermissionsGrouped(): array
    {
        return self::$permissionsByRole['admin'];
    }

    public static function usersWithAccessToProject($project): array
    {
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

        return collect([
            ...$admins,
            ...$owners,
            ...$givenAccess,
        ])
            ->unique('id')
            ->sortBy('name')
            ->toArray();
    }
}
