<?php

namespace App\Services;

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
}
