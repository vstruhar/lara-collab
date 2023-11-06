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
        ],
        'manager' => ['view users'],
        'developer' => [],
        'designer' => [],
        'client' => [],
    ];

    public static function allPermissionsGrouped(): array
    {
        return self::$permissionsByRole['admin'];
    }

    public static function usersWithAccessToProject($project): array
    {
        $admins = User::role('admin')->get(['id', 'name', 'avatar'])->map(fn ($user) => [...$user->toArray(), 'reason' => 'admin']);
        $owners = $project->clientCompany->clients->map(fn ($user) => [...$user->toArray(), 'reason' => 'client']);
        $givenAccess = $project->users->map(fn ($user) => [...$user->toArray(), 'reason' => 'given access']);

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
