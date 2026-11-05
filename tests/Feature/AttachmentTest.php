<?php

use App\Events\Task\AttachmentDeleted;
use App\Events\Task\AttachmentsUploaded;
use App\Models\Attachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    resetPermissionServiceCache();
    seedAccessControl();
    createOwnerCompany();
    $this->admin = createAdmin();
    $this->actingAs($this->admin);

    [
        'project' => $this->project,
        'task' => $this->task,
    ] = createProjectWithTask();
});

it('uploads and deletes an attachment', function () {
    Event::fake([AttachmentsUploaded::class, AttachmentDeleted::class]);
    Storage::fake();

    $response = $this->postJson(route('projects.tasks.attachments.upload', [$this->project, $this->task]), [
        'attachments' => [
            UploadedFile::fake()->create('spec.pdf', 20, 'application/pdf'),
        ],
    ])->assertOk()
        ->assertJsonPath('files.0.name', 'spec.pdf');

    $attachment = Attachment::find($response->json('files.0.id'));

    expect($attachment)->not->toBeNull()
        ->and($attachment->task_id)->toBe($this->task->id);

    Storage::assertExists('public'.str_replace('/storage', '', $attachment->path));

    $this->deleteJson(route('projects.tasks.attachments.destroy', [$this->project, $this->task, $attachment]))
        ->assertOk();

    expect(Attachment::find($attachment->id))->toBeNull();
    Event::assertDispatched(AttachmentsUploaded::class);
    Event::assertDispatched(AttachmentDeleted::class);
});
