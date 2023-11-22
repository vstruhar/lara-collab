<?php

namespace App\Services;

class NotificationService
{
    public static function getLatest(int $limit)
    {
        if (! auth()->check()) {
            return null;
        }
        /** @var User */
        $user = auth()->user();

        return $user
            ->notifications()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    ...$notification->data,
                    'id' => $notification->id,
                    'read_at' => $notification->read_at,
                ];
            });
    }
}
