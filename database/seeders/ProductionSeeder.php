<?php

namespace Database\Seeders;

use App\Models\OwnerCompany;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()
            ->create(['email' => 'admin@mail.com', 'job_title' => 'Owner'])
            ->assignRole(Role::firstWhere('name', 'admin'));

        OwnerCompany::create([
            'name' => '',
            'logo' => null,
            'address' => '',
            'postal_code' => '',
            'city' => '',
            'country_id' => null,
            'currency_id' => 97,
            'phone' => '',
            'web' => '',
            'tax' => 0,
            'email' => '',
            'iban' => '',
            'swift' => '',
            'business_id' => '',
            'tax_id' => '',
            'vat' => '',
        ]);
    }
}
