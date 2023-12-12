<?php

namespace App\Http\Controllers\MyWork;

use App\Http\Controllers\Controller;
use App\Http\Resources\Activity\ActivityGroupedByDateCollection;
use App\Models\Activity;
use App\Models\Project;
use App\Services\PermissionService;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    public function index(): Response
    {
        /** @var \App\Models\User */
        $user = auth()->user();

        $projects = PermissionService::projectsThatUserCanAccess($user);

        return Inertia::render('MyWork/Activity/Index', [
            'groupedActivities' => new ActivityGroupedByDateCollection(
                Activity::whereIn('project_id', $projects->pluck('id'))
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
                    Project::dropdownValues(),
                    ['value' => '0', 'label' => 'All projects']
                ),
            ],
        ]);
    }
}
