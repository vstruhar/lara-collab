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
        User::create([
            'email' => 'admin@mail.com',
            'name' => 'Admin',
            'phone' => '',
            'rate' => 0,
            'job_title' => 'Owner',
            'avatar' => null,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => null,
        ])->assignRole(Role::firstWhere('name', 'admin'));

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
