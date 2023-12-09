<?php

namespace Database\Seeders;

use App\Models\OwnerCompany;
use Illuminate\Database\Seeder;

class OwnerCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OwnerCompany::create([
            'name' => fake()->company,
            'logo' => null,
            'address' => fake()->streetAddress,
            'postal_code' => fake()->postcode,
            'city' => fake()->city,
            'country_id' => fake()->numberBetween(1, 249),
            'currency_id' => 97,
            'phone' => fake()->phoneNumber,
            'web' => 'https://company.com',
            'tax' => 1000, // 10%
            'email' => fake()->email,
            'iban' => fake()->iban,
            'swift' => fake()->swiftBicNumber,
            'business_id' => '111111111',
            'tax_id' => '222222222',
            'vat' => '333333333',
        ]);
    }
}
