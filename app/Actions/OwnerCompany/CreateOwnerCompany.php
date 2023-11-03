<?php

namespace App\Actions\OwnerCompany;

use App\Models\OwnerCompany;

class CreateOwnerCompany
{
    public function update($request): bool
    {
        $ownerCompany = OwnerCompany::first();

        $data = $request->except(['logo']);

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logo->storePubliclyAs('public/company', 'logo.'.$logo->clientExtension());
            $data['logo'] = '/storage/company/logo.'.$logo->clientExtension();
        }

        return $ownerCompany->update($data);
    }
}
