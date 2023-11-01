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
            ['name' => 'New', 'color' => '#1864AB'],
            ['name' => 'Confirmed', 'color' => '#5F3DC4'],
            ['name' => 'Estimate', 'color' => '#862E9C'],
            ['name' => 'Blocked', 'color' => '#A61E4D'],
            ['name' => 'Bug', 'color' => '#C92A2A'],
            ['name' => 'In progress', 'color' => '#E67700'],
            ['name' => 'Deployed', 'color' => '#099268'],
            ['name' => 'Done', 'color' => '#2B8A3E'],
        ]);
    }
}
