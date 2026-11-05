<?php

namespace App\Http\Controllers\MyWork;

use App\Http\Controllers\Controller;
use App\Http\Resources\Activity\ActivityGroupedByDateCollection;
use App\Models\Activity;
use App\Models\Project;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        Gate::allowIf(fn (User $user) => $user->can('view activities'));

        $projectIds = PermissionService::projectsThatUserCanAccess($user)->pluck('id');

        return Inertia::render('MyWork/Activity/Index', [
            'groupedActivities' => new ActivityGroupedByDateCollection(
                Activity::query()
                    ->whereIn('project_id', $projectIds)
                    ->visibleTo($user)
                    ->filterByQueryString()
                    ->with([
                        'activityCapable',
                        'project',
                    ])
                    ->latest()
                    ->limit(100)
                    ->get()
            ),
            'dropdowns' => [
                'projects' => Arr::prepend(
                    Project::dropdownValues($projectIds->all()),
                    ['value' => '0', 'label' => 'All projects'],
                ),
            ],
        ]);
    }
}
