<?php

namespace App\Actions\Task;

use App\Enums\PricingType;
use App\Events\Task\AttachmentsUploaded;
use App\Events\Task\TaskCreated;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Throwable;

class CreateTask
{
    public function create(Project $project, array $data): Task
    {
        return DB::transaction(function () use ($project, $data) {
            if (isset($data['pricing_type']) && $data['pricing_type'] === PricingType::HOURLY->value) {
                $data['fixed_price'] = null;
            } elseif (isset($data['fixed_price']) && isset($data['pricing_type']) && $data['pricing_type'] === PricingType::FIXED->value) {
                $data['fixed_price'] = (int) ($data['fixed_price'] * 100);
            }

            $task = $project->tasks()->create([
                'group_id' => $data['group_id'],
                'created_by_user_id' => auth()->id(),
                'assigned_to_user_id' => $data['assigned_to_user_id'],
                'name' => $data['name'],
                'number' => $project->tasks()->withArchived()->count() + 1,
                'description' => $data['description'],
                'due_on' => $data['due_on'],
                'estimation' => $data['estimation'],
                'pricing_type' => $data['pricing_type'],
                'fixed_price' => $data['fixed_price'],
                'hidden_from_clients' => $data['hidden_from_clients'],
                'billable' => $data['billable'],
                'completed_at' => null,
            ]);

            $task->subscribedUsers()->attach($data['subscribed_users'] ?? []);

            $task->labels()->attach($data['labels'] ?? []);

            if (! empty($data['attachments'])) {
                $this->uploadAttachments($task, $data['attachments'], false);
            }

            TaskCreated::dispatch($task);

            return $task;
        });
    }

    public function uploadAttachments(Task $task, array $items, $dispatchEvent = true): Collection
    {
        $rows = collect($items)
            ->map(function (UploadedFile $item) use ($task) {
                $filename = strtolower(Str::ulid()).'.'.$item->getClientOriginalExtension();
                $filepath = "tasks/{$task->id}/{$filename}";

                $item->storeAs('public', $filepath);

                $thumbFilepath = $this->generateThumb($item, $task, $filename);

                return [
                    'user_id' => auth()->id(),
                    'name' => $item->getClientOriginalName(),
                    'path' => "/storage/$filepath",
                    'thumb' => $thumbFilepath ? "/storage/$thumbFilepath" : null,
                    'type' => $item->getClientMimeType(),
                    'size' => $item->getSize(),
                ];
            });

        $attachments = $task->attachments()->createMany($rows);

        $task->activities()->create([
            'project_id' => $task->project_id,
            'user_id' => auth()->id(),
            'title' => ($attachments->count() > 1 ? 'Attachments where' : 'Attachment was').' uploaded',
            'subtitle' => "to \"{$task->name}\" by ".auth()->user()->name,
        ]);

        if ($dispatchEvent) {
            AttachmentsUploaded::dispatch($task, $attachments);
        }

        return $attachments;
    }

    protected function generateThumb(UploadedFile $file, Task $task, string $filename)
    {
        if (in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
            try {
                $thumbFilepath = "tasks/{$task->id}/thumbs/{$filename}";

                $image = Image::make($file->get())
                    ->fit(100, 100)
                    ->encode(null, 75);

                Storage::put("public/$thumbFilepath", $image);

                return $thumbFilepath;
            } catch (Throwable $e) {
                return null;
            }
        }

        return null;
    }
}
