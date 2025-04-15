<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Services\PermissionService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $projectIds = PermissionService::projectsThatUserCanAccess(auth()->user())->pluck('id');

        return Inertia::render('Dashboard/Index', [
            'projects' => Project::whereIn('id', $projectIds)
                ->with([
                    'clientCompany:id,name',
                ])
                ->withCount([
                    'tasks AS all_tasks_count',
                    'tasks AS completed_tasks_count' => fn ($query) => $query->whereNotNull('completed_at'),
                    'tasks AS overdue_tasks_count' => fn ($query) => $query->whereNull('completed_at')->whereDate('due_on', '<', now()),
                ])
                ->withExists('favoritedByAuthUser AS favorite')
                ->orderBy('favorite', 'desc')
                ->orderBy('name', 'asc')
                ->get(['id', 'name']),
            'overdueTasks' => Task::whereIn('project_id', $projectIds)
                ->whereNull('completed_at')
                ->whereDate('due_on', '<', now())
                ->whereHas('assignedUsers', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->with('project:id,name')
                ->with('taskGroup:id,name')
                ->orderBy('due_on')
                ->get(['id', 'name', 'due_on', 'group_id', 'project_id']),
            'recentlyAssignedTasks' => Task::whereIn('project_id', $projectIds)
                ->whereNull('completed_at')
                ->whereNotNull('assigned_at')
                ->whereHas('assignedUsers', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->with([
                    'assignedUsers' => function ($query) {
                        $query->where('user_id', auth()->id());
                    },
                ])
                ->with('project:id,name')
                ->with('taskGroup:id,name')
                ->orderBy('assigned_at')
                ->limit(10)
                ->get(['id', 'name', 'assigned_at', 'group_id', 'project_id'])
                ->sortByDesc('assignedUsers.*.created_at'),
            'recentComments' => Comment::query()
                ->whereHas('task', function ($query) use ($projectIds) {
                    $query->whereIn('project_id', $projectIds)
                        ->whereHas('assignedUsers', function ($query) {
                            $query->where('user_id', auth()->id());
                        });
                })
                ->with([
                    'task:id,name,project_id',
                    'task.project:id,name',
                    'user:id,name',
                ])
                ->latest()
                ->get(),
        ]);
    }
}
