<?php

namespace App\Actions\Task;

use App\Events\TaskCreated;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateTask
{
    public function create(Project $project, array $data): Task
    {
        return DB::transaction(function () use ($project, $data) {
            $task = $project->tasks()->create([
                'group_id' => $data['group_id'],
                'created_by_user_id' => auth()->id(),
                'assigned_to_user_id' => $data['assigned_to_user_id'],
                'name' => $data['name'],
                'number' => $project->tasks()->count(),
                'description' => $data['description'],
                'due_on' => $data['due_on'],
                'estimation' => $data['estimation'],
                'hidden_from_clients' => $data['hidden_from_clients'],
                'billable' => $data['billable'],
                'completed_at' => null,
            ]);

            $task->subscribedUsers()->attach($data['subscribers']);

            $task->labels()->attach($data['labels']);

            $this->uploadAttachments($task, $data['attachments']);

            TaskCreated::dispatch($task);

            return $task;
        });
    }

    public function uploadAttachments(Task $task, array $items): Collection
    {
        $rows = collect($items)
            ->map(function (UploadedFile $item) use ($task) {
                $filename = strtolower(Str::ulid()).'.'.$item->getClientOriginalExtension();
                $filepath = "tasks/{$task->id}/{$filename}";

                $item->storeAs('public', $filepath);

                return [
                    'user_id' => auth()->id(),
                    'name' => $item->getClientOriginalName(),
                    'path' => "/storage/$filepath",
                    'type' => $item->getClientMimeType(),
                    'size' => $item->getSize(),
                ];
            });

        return $task->attachments()->createMany($rows);
    }
}
