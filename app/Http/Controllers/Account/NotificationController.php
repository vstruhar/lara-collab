<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Notifications/Index', []);
    }

    public function read(DatabaseNotification $notification)
    {
        $notification->markAsRead();

        return response()->json();
    }

    public function readAll()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json();
    }
}
