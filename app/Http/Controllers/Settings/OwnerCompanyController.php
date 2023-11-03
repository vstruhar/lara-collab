<?php

namespace App\Http\Controllers\Settings;

use App\Actions\OwnerCompany\CreateOwnerCompany;
use App\Http\Controllers\Controller;
use App\Http\Requests\OwnerCompany\UpdateOwnerCompanyRequest;
use App\Models\Country;
use App\Models\Currency;
use App\Models\OwnerCompany;
use Inertia\Inertia;

class OwnerCompanyController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        return Inertia::render('Settings/Company/Edit', [
            'item' => OwnerCompany::first(),
            'countries' => Country::dropdownValues(),
            'currencies' => Currency::dropdownValues(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOwnerCompanyRequest $request)
    {
        $this->authorize(OwnerCompanyPolicy::class);

        (new CreateOwnerCompany)->update($request);

        return redirect()->back()->success('Company updated', 'The company was successfully updated.');
    }
}
