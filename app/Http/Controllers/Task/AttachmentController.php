<?php

namespace App\Http\Controllers\Task;

use App\Actions\Task\CreateTask;
use App\Events\Task\AttachmentDeleted;
use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AttachmentController extends Controller
{
    public function store(Request $request, Project $project, Task $task): JsonResponse
    {
        $files = (new CreateTask)->uploadAttachments($task, $request->attachments);

        return response()->json(['files' => $files]);
    }

    public function destroy(Project $project, Task $task, Attachment $attachment): JsonResponse
    {
        File::delete(public_path($attachment->path));
        File::delete(public_path($attachment->thumb));

        $attachment->delete();

        AttachmentDeleted::dispatch($task, $attachment->id);

        return response()->json();
    }
}
