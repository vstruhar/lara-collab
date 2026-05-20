<?php

namespace App\Http\Controllers\MyWork;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MyWorkTaskController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var User */
        $user = auth()->user();

        $projects = PermissionService::projectsThatUserCanAccess($user);

        $prioritySort = data_get($request->input('sort', []), 'priority');

        return Inertia::render('MyWork/Tasks/Index', [
            'projects' => Project::whereIn('id', $projects->pluck('id'))
                ->with([
                    'clientCompany:id,name',
                    'tasks' => function ($query) use ($user, $prioritySort) {
                        $query->when($user->hasRole('client'), fn ($query) => $query->where('hidden_from_clients', false))
                            ->where('assigned_to_user_id', $user->id)
                            ->whereNull('completed_at')
                            ->withoutGlobalScope('ordered')
                            ->when($prioritySort, function ($query, $direction) {
                                $direction = $direction === 'asc' ? 'asc' : 'desc';

                                $query
                                    ->leftJoin('task_priorities', 'tasks.priority_id', '=', 'task_priorities.id')
                                    ->orderByRaw('tasks.priority_id IS NULL')
                                    ->orderBy('task_priorities.order', $direction)
                                    ->orderByRaw('-tasks.due_on DESC')
                                    ->select('tasks.*');
                            }, function ($query) {
                                $query->orderByRaw('-due_on DESC');
                            })
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
