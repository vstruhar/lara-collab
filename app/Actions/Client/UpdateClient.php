<?php

namespace App\Actions\Client;

use App\Services\UserService;
use Illuminate\Support\Facades\Hash;

class UpdateClient
{
    public function update($user, array $data): bool
    {
        $newData = [
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
        ];

        if ($user->avatar === null || $data['avatar']) {
            $newData['avatar'] = UserService::storeOrFetchAvatar($user, $data['avatar']);
        }

        if (! empty($data['password'])) {
            $newData['password'] = Hash::make($data['password']);
        }

        if (! empty($data['companies'])) {
            $user->clientCompanies()->sync($data['companies']);
        }

        return $user->update($newData);
    }
}
