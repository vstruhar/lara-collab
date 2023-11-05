<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\Role\RoleResource;
use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Role::class, 'role');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Settings/Roles/Index', [
            'items' => RoleResource::collection(
                Role::searchByQueryString()
                    ->sortByQueryString()
                    ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
                    ->withCount('permissions')
                    ->paginate(12),
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Settings/Roles/Create', [
            'allPermissionsGrouped' => PermissionService::allPermissionsGrouped(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
        $role->syncPermissions($request->permissions);

        return redirect()->route('settings.roles.index')->success('Role created', 'A new role was successfully created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        return Inertia::render('Settings/Roles/Edit', [
            'item' => new RoleResource($role),
            'allPermissionsGrouped' => PermissionService::allPermissionsGrouped(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Role $role, UpdateRoleRequest $request)
    {
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('settings.roles.index')->success('Role updated', 'The role was successfully updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $usersWithRole = DB::table('model_has_roles')->where('role_id', $role->id)->exists();

        if ($usersWithRole) {
            return redirect()->route('settings.roles.index')->warning('Action stopped', 'You cannot archive a role that is currently assigned to users.');
        }
        $role->archive();

        return redirect()->back()->success('Role archived', 'The role was successfully archived.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(int $roleId)
    {
        $role = Role::withArchived()->findOrFail($roleId);

        $this->authorize('restore', $role);

        $role->unArchive();

        return redirect()->back()->success('Role restored', 'The restoring of the role was completed successfully.');
    }
}
