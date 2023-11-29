<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationGroupedByDateCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this
            ->collection
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'],
                    'subtitle' => $notification->data['subtitle'],
                    'link' => $notification->data['link'],
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                    'date' => $notification->created_at->format('F j, Y'),
                ];
            })
            ->groupBy('date')
            ->toArray();
    }
}
