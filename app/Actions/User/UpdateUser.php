<?php

namespace App\Actions\User;

use App\Services\UserService;
use Illuminate\Support\Facades\Hash;

class UpdateUser
{
    public function update($user, array $data): bool
    {
        $newData = [
            'name' => $data['name'],
            'job_title' => $data['job_title'],
            'phone' => $data['phone'],
            'rate' => $data['rate'] * 100,
            'email' => $data['email'],
        ];

        $user->syncRoles($data['roles']);

        if ($user->avatar === null || $data['avatar']) {
            $newData['avatar'] = UserService::storeOrFetchAvatar($user, $data['avatar']);
        }

        if (! empty($data['password'])) {
            $newData['password'] = Hash::make($data['password']);
        }

        return $user->update($newData);
    }
}
