<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\Project\ProjectResource;
use App\Models\ClientCompany;
use App\Models\Currency;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Project::class, 'project');
    }

    public function index(Request $request)
    {
        return Inertia::render('Projects/Index', [
            'items' => ProjectResource::collection(
                Project::searchByQueryString()
                    ->when($request->user()->isNotAdmin(), function ($query) {
                        $query->whereHas('clientCompany.clients', fn ($query) => $query->where('users.id', auth()->id()))
                            ->orWhereHas('users', fn ($query) => $query->where('id', auth()->id()));
                    })
                    ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
                    ->with([
                        'clientCompany:id,name',
                        'clientCompany.clients:id,name,avatar',
                        'users:id,name,avatar',
                    ])
                    ->withCount([
                        'tasks AS all_tasks_count',
                        'tasks AS completed_tasks_count' => fn ($query) => $query->whereNotNull('completed_at'),
                        'tasks AS overdue_tasks_count' => fn ($query) => $query->whereNull('completed_at')->whereDate('due_on', '<', now()),
                    ])
                    ->withExists('favoritedByAuthUser AS favorite')
                    ->orderBy('favorite', 'desc')
                    ->orderBy('name', 'asc')
                    ->get()
            ),
        ]);
    }

    public function create()
    {
        return Inertia::render('Projects/Create', [
            'dropdowns' => [
                'companies' => ClientCompany::dropdownValues(),
                'users' => User::userDropdownValues(),
                'currencies' => Currency::dropdownValues(['with' => ['clientCompanies:id,currency_id']]),
            ],
        ]);
    }

    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();

        $data['rate'] *= 100;

        $project = Project::create($data);

        $project->users()->attach($data['users']);

        $project->taskGroups()->createMany([
            ['name' => 'Backlog'],
            ['name' => 'Todo'],
            ['name' => 'In progress'],
            ['name' => 'QA'],
            ['name' => 'Done'],
            ['name' => 'Deployed'],
        ]);

        return redirect()->route('projects.index')->success('Project created', 'A new project was successfully created.');
    }

    public function edit(Project $project)
    {
        return Inertia::render('Projects/Edit', [
            'item' => $project,
            'dropdowns' => [
                'companies' => ClientCompany::dropdownValues(),
                'users' => User::userDropdownValues(),
                'currencies' => Currency::dropdownValues(['with' => ['clientCompanies:id,currency_id']]),
            ],
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $data = $request->validated();

        $data['rate'] *= 100;

        $project->update($data);

        $project->users()->sync($data['users']);

        return redirect()->route('projects.index')->success('Project updated', 'The project was successfully updated.');
    }

    public function destroy(Project $project)
    {
        $project->archive();

        return redirect()->back()->success('Project archived', 'The project was successfully archived.');
    }

    public function restore(int $projectId)
    {
        $project = Project::withArchived()->findOrFail($projectId);

        $this->authorize('restore', $project);

        $project->unArchive();

        return redirect()->back()->success('Project restored', 'The restoring of the project was completed successfully.');
    }

    public function favoriteToggle(Project $project)
    {
        request()->user()->toggleFavorite($project);

        return redirect()->back();
    }

    public function userAccess(Request $request, Project $project)
    {
        $this->authorize('editUserAccess', $project);

        $userIds = array_merge(
            $request->get('users', []),
            $request->get('clients', [])
        );

        (new ProjectService($project))->updateUserAccess($userIds);

        return redirect()->back();
    }
}
