<?php

namespace App\Http\Controllers\MyWork;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\PermissionService;
use Inertia\Inertia;
use Inertia\Response;

class MyWorkTaskController extends Controller
{
    public function index(): Response
    {
        /** @var \App\Models\User */
        $user = auth()->user();

        $projects = PermissionService::projectsThatUserCanAccess($user);

        return Inertia::render('MyWork/Tasks/Index', [
            'projects' => Project::whereIn('id', $projects->pluck('id'))
                ->with([
                    'clientCompany:id,name',
                    'tasks' => function ($query) use ($user) {
                        $query->when($user->hasRole('client'), fn ($query) => $query->where('hidden_from_clients', false))
                            ->where('assigned_to_user_id', $user->id)
                            ->whereNull('completed_at')
                            ->withoutGlobalScope('ordered')
                            ->orderByRaw('-due_on DESC')
                            ->with([
                                'labels:id,name,color',
                                'assignedToUser:id,name',
                                'taskGroup:id,name',
                            ]);
                    },
                ])
                ->withExists('favoritedByAuthUser AS favorite')
                ->orderBy('favorite', 'desc')
                ->orderBy('name', 'asc')
                ->get(),
        ]);
    }
}
