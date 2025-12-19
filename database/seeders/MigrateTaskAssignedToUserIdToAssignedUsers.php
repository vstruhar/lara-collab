<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class MigrateTaskAssignedToUserIdToAssignedUsers extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Auth::loginUsingId(1);
        $tasks = Task::get();
        foreach ($tasks as $task) {
            if ($task->assigned_to_user_id == null) {
                continue;
            }
            $task->assignees()->sync([$task->assigned_to_user_id]);
            $task->update(['assigned_to_user_id' => null]);
        }
    }
}