<?php

namespace App\Http\Controllers\Task;

use App\Actions\Task\CreateTask;
use App\Events\Task\AttachmentDeleted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskAttachmentService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function store(StoreAttachmentRequest $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('viewAny', [Attachment::class, $project]);

        $files = (new CreateTask)->uploadAttachments($task, $request->file('attachments', []));

        return response()->json(['files' => $files]);
    }

    public function download(Attachment $attachment, TaskAttachmentService $attachments): StreamedResponse
    {
        $this->authorizeView($attachment);

        return $attachments->downloadResponse($attachment);
    }

    public function thumb(Attachment $attachment, TaskAttachmentService $attachments): StreamedResponse
    {
        $this->authorizeView($attachment);

        return $attachments->thumbResponse($attachment);
    }

    public function destroy(Project $project, Task $task, Attachment $attachment, TaskAttachmentService $attachments): JsonResponse
    {
        $this->authorize('delete', [$attachment, $project]);

        $attachments->delete($attachment);

        $attachment->delete();

        AttachmentDeleted::dispatch($task, $attachment->id);

        return response()->json();
    }

    private function authorizeView(Attachment $attachment): void
    {
        $task = Task::withArchived()
            ->with(['project' => fn ($query) => $query->withArchived()])
            ->find($attachment->task_id);

        $user = request()->user();

        if (! $task instanceof Task || $task->project === null || ! $user instanceof User || $task->isHiddenFrom($user)) {
            abort(404);
        }

        $this->authorize('viewAny', [Attachment::class, $task->project]);
    }
}
