<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DropdownValuesController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $dropdowns = collect();

        $dropdowns->when($request->has('users'), function (Collection $collection) {
            return $collection->put('users', User::userDropdownValues());
        });

        $dropdowns->when($request->has('clients'), function (Collection $collection) {
            return $collection->put('clients', User::clientDropdownValues());
        });

        $dropdowns->when($request->has('mentionProjectUsers'), function (Collection $collection) use ($request) {
            $project = Project::findOrFail($request->integer('projectId'));
            $this->authorize('view', $project);

            return $collection->put(
                'mentionProjectUsers',
                PermissionService::usersWithAccessToProject($project)->pluck('name'),
            );
        });

        return response()->json($dropdowns);
    }
}
