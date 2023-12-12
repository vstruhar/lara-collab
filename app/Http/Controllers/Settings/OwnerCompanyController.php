<?php

namespace App\Http\Controllers\Settings;

use App\Actions\OwnerCompany\UpdateOwnerCompany;
use App\Http\Controllers\Controller;
use App\Http\Requests\OwnerCompany\UpdateOwnerCompanyRequest;
use App\Models\Country;
use App\Models\Currency;
use App\Models\OwnerCompany;
use Inertia\Inertia;

class OwnerCompanyController extends Controller
{
    public function edit()
    {
        $this->authorize('view', OwnerCompany::class);

        return Inertia::render('Settings/Company/Edit', [
            'item' => OwnerCompany::first(),
            'dropdowns' => [
                'countries' => Country::dropdownValues(),
                'currencies' => Currency::dropdownValues(),
            ],
        ]);
    }

    public function update(UpdateOwnerCompanyRequest $request)
    {
        $this->authorize('update', OwnerCompany::class);

        (new UpdateOwnerCompany)->update($request);

        return redirect()->back()->success('Company updated', 'The company was successfully updated.');
    }
}
