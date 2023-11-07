<?php

namespace Database\Seeders;

use App\Models\Label;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Label::insert([
            ['name' => 'Confirmed', 'color' => '#37B24D'],
            ['name' => 'Estimate', 'color' => '#AE3EC9'],
            ['name' => 'Blocked', 'color' => '#F03E3E'],
            ['name' => 'Bug', 'color' => '#D6336C'],
            ['name' => 'Rework', 'color' => '#F76707'],
        ]);
    }
}
