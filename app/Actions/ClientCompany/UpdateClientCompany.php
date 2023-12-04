<?php

namespace App\Actions\ClientCompany;

use App\Models\ClientCompany;

class UpdateClientCompany
{
    public function update(ClientCompany $clientCompany, array $data): bool
    {
        if (! empty($data['clients'])) {
            $clientCompany->clients()->sync($data['clients']);
        }

        $data['rate'] *= 100;

        return $clientCompany->update($data);
    }
}
