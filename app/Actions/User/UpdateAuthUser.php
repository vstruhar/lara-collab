<?php

namespace App\Actions\User;

use App\Services\UserService;
use Illuminate\Support\Facades\Hash;

class UpdateAuthUser
{
    public function update($user, array $data): bool
    {
        $newData = [
            'name' => $data['name'],
            'job_title' => $data['job_title'],
            'phone' => $data['phone'],
            'email' => $data['email'],
        ];

        if ($user->avatar === null || $data['avatar']) {
            $newData['avatar'] = UserService::storeOrFetchAvatar($user, $data['avatar']);
        }

        if (! empty($data['password'])) {
            $newData['password'] = Hash::make($data['password']);
        }

        return $user->update($newData);
    }
}
