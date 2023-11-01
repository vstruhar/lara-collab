<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PermissionService
{
    public static function allPermissionsGrouped(): array
    {
        $permissions = Permission::pluck('name');

        $modelNames = collect(File::files(app_path('Models')))
            ->map(fn ($item) => Str::of($item->getFilename())->replace('.php', '')->lower()->toString())
            ->reject('permission')
            ->sort();

        return $modelNames
            ->mapWithKeys(function (string $permission) use ($permissions) {
                return [
                    $permission => $permissions
                        ->filter(fn ($p) => Str::contains($p, $permission))
                        ->sort()
                        ->values()
                        ->toArray(),
                ];
            })
            ->toArray();
    }
}
