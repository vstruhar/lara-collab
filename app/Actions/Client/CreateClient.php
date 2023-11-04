<?php

namespace App\Actions\Client;

use App\Events\UserCreated;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateClient
{
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'job_title' => 'Client',
                'phone' => $data['phone'],
                'rate' => null,
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->update(['avatar' => UserService::storeOrFetchAvatar($user, $data['avatar'])]);

            $user->assignRole('client');

            if (! empty($data['companies'])) {
                $user->clientCompanies()->attach($data['companies']);
            }

            UserCreated::dispatch($user, $data['password']);

            return $user;
        });
    }
}
