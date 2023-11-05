<?php

namespace App\Http\Controllers\Client;

use App\Actions\ClientCompany\CreateClientCompany;
use App\Actions\ClientCompany\UpdateClientCompany;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientCompany\StoreClientCompanyRequest;
use App\Http\Requests\ClientCompany\UpdateClientCompanyRequest;
use App\Http\Resources\ClientCompany\ClientCompanyResource;
use App\Models\ClientCompany;
use App\Models\Country;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientCompanyController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(ClientCompany::class, 'company');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Clients/Companies/Index', [
            'items' => ClientCompanyResource::collection(
                ClientCompany::searchByQueryString()
                    ->sortByQueryString()
                    ->with('clients')
                    ->when($request->has('archived'), fn ($query) => $query->onlyArchived())
                    ->paginate(12)
            ),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Clients/Companies/Create', [
            'dropdowns' => [
                'clients' => User::clientDropdownValues(),
                'countries' => Country::dropdownValues(),
                'currencies' => Currency::dropdownValues(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientCompanyRequest $request)
    {
        (new CreateClientCompany)->create($request->validated());

        return redirect()->route('clients.companies.index')->success('Company created', 'A new company was successfully created.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClientCompany $company)
    {
        return Inertia::render('Clients/Companies/Edit', [
            'item' => new ClientCompanyResource($company),
            'dropdowns' => [
                'clients' => User::clientDropdownValues(),
                'countries' => Country::dropdownValues(),
                'currencies' => Currency::dropdownValues(),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClientCompany $company, UpdateClientCompanyRequest $request)
    {
        (new UpdateClientCompany)->update($company, $request->validated());

        return redirect()->route('clients.companies.index')->success('Company updated', 'The company was successfully updated.');
    }

    /**
     * Archive the specified resource from storage.
     */
    public function destroy(ClientCompany $company)
    {
        $company->archive();

        return redirect()->back()->success('Company archived', 'The company was successfully archived.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(int $companyId)
    {
        $clientCompany = ClientCompany::withArchived()->findOrFail($companyId);

        $this->authorize('restore', $clientCompany);

        $clientCompany->unArchive();

        return redirect()->back()->success('Company restored', 'The restoring of the company was completed successfully.');
    }
}
