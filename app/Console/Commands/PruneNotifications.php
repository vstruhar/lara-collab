<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PruneNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:prune-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will remove outdated notifications for every user.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $skip = 50;

        User::all()
            ->each(function (User $user) use ($skip) {
                $count = $user->notifications()->count();

                if ($count > $skip) {
                    $user->notifications()
                        ->latest()
                        ->skip($skip)
                        ->take($count - $skip)
                        ->delete();
                }
            });
    }
}
