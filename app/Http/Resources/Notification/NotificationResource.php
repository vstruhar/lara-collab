<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->data['title'],
            'subtitle' => $this->data['subtitle'],
            'link' => $this->data['link'],
            'read_at' => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }
}
