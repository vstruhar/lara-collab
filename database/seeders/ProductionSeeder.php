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
            'email' => config('auth.admin.email'),
            'name' => 'Crystal Desarrollo S.R.L.',
            'phone' => '',
            'rate' => 0,
            'job_title' => 'Owner',
            'avatar' => null,
            'password' => bcrypt(config('auth.admin.password')),
            'remember_token' => null,
        ])->assignRole(Role::firstWhere('name', 'admin'));

        OwnerCompany::create([
            'name' => 'Crystal Desarrollo S.R.L.',
            'logo' => null,
            'address' => '',
            'postal_code' => '',
            'city' => 'San Salvador de Jujuy',
            'country_id' => null,
            'currency_id' => 97,
            'phone' => '',
            'web' => 'https://crystal-desarrollo.com',
            'tax' => 0,
            'email' => 'contacto@crystal-desarrollo.com',
            'iban' => '',
            'swift' => '',
            'business_id' => '',
            'tax_id' => '',
            'vat' => '',
        ]);
    }
}
