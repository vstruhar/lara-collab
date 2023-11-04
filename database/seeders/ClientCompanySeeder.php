<?php

namespace Database\Seeders;

use App\Models\ClientCompany;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::role('client')
            ->get()
            ->each(function (User $client) {
                ClientCompany::factory()->create()->clients()->attach($client);
            });

    }
}
