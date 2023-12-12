<?php

namespace App\Http\Controllers\Client;

use App\Actions\Client\CreateClient;
use App\Actions\Client\UpdateClient;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Resources\Client\ClientResource;
use App\Models\ClientCompany;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientUserController extends Controller
{
    public function index(Request $request): Response
    {
        abort_if(! $request->user()->can('view client users'), 401);

        return Inertia::render('Clients/Users/Index', [
            'items' => ClientResource::collection(
                User::searchByQueryString()
                    ->sortByQueryString()
                    ->role('client')
                    ->with('clientCompanies')
                    ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
                    ->paginate(12)
            ),
        ]);
    }

    public function create()
    {
        abort_if(! request()->user()->can('create client user'), 401);

        return Inertia::render('Clients/Users/Create', [
            'dropdowns' => [
                'companies' => ClientCompany::dropdownValues(),
            ],
        ]);
    }

    public function store(StoreClientRequest $request)
    {
        abort_if(! request()->user()->can('create client user'), 401);

        $client = (new CreateClient)->create($request->validated());

        if (empty($request->companies)) {
            return redirect()
                ->route('clients.companies.create', ['client_id' => $client->id])
                ->success('Client created', 'A new client was successfully created. Now you can create a company for the client.');
        }

        return redirect()->route('clients.users.index')->success('Client created', 'A new client was successfully created.');
    }

    public function edit(User $user)
    {
        abort_if(! request()->user()->can('edit client user'), 401);

        return Inertia::render('Clients/Users/Edit', [
            'item' => new ClientResource($user),
            'dropdowns' => [
                'companies' => ClientCompany::dropdownValues(),
            ],
        ]);
    }

    public function update(User $user, UpdateClientRequest $request)
    {
        abort_if(! request()->user()->can('edit client user'), 401);

        (new UpdateClient)->update($user, $request->validated());

        return redirect()->route('clients.users.index')->success('Client updated', 'The client was successfully updated.');
    }

    public function destroy(User $user)
    {
        abort_if(! request()->user()->can('archive client user'), 401);

        if (auth()->id() === $user->id) {
            return redirect()->route('clients.users.index')->warning('Action stopped', 'You cannot archive the client with whom you are currently logged in.');
        }
        $user->archive();

        return redirect()->back()->success('Client archived', 'The client was successfully archived.');
    }

    public function restore(int $userId)
    {
        abort_if(! request()->user()->can('restore client user'), 401);

        $user = User::withArchived()->findOrFail($userId);

        $user->unArchive();

        return redirect()->back()->success('Client restored', 'The restoring of the client was completed successfully.');
    }
}
