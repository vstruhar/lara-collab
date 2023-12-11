<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    private array $jobTitleToRole = [
        'Frontend Developer' => 'developer',
        'Backend Developer' => 'developer',
        'Fullstack Developer' => 'developer',
        'QA Engineer' => 'qa engineer',
        'Designer' => 'designer',
        'Client' => 'client',
        'Manager' => 'manager',
        'Owner' => 'admin',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolesExceptClient = collect(RoleSeeder::$roles)
            ->filter(fn ($i) => $i !== 'client')
            ->toArray();

        foreach ($rolesExceptClient as $role) {
            User::factory()
                ->create(['email' => "$role@mail.com", 'job_title' => $this->getJobTitle($role)])
                ->assignRole($role);
        }

        User::factory(20)
            ->create()
            ->each(fn (User $user) => $user->assignRole($this->jobTitleToRole[$user->job_title]));
    }

    private function getJobTitle(string $role): string
    {
        foreach ($this->jobTitleToRole as $title => $value) {
            if ($role === $value) {
                return $title;
            }
        }
    }
}
