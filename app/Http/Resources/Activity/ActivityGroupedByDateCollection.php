<?php

namespace App\Http\Resources\Activity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ActivityGroupedByDateCollection extends ResourceCollection
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
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'activity_capable' => $activity->activityCapable,
                    'project' => $activity->project,
                    'title' => $activity->title,
                    'subtitle' => $activity->subtitle,
                    'created_at' => $activity->created_at,
                    'date' => $activity->created_at->format('F j, Y'),
                ];
            })
            ->groupBy('date')
            ->toArray();
    }
}
