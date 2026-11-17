<?php

use App\Events\Task\AttachmentDeleted;
use App\Events\Task\AttachmentsUploaded;
use App\Models\Attachment;
use App\Models\ClientCompany;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskGroup;
use App\Models\User;
use App\Services\TaskAttachmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    resetPermissionServiceCache();
    seedAccessControl();
    createOwnerCompany();
    $this->admin = createAdmin();
    $this->actingAs($this->admin);

    [
        'company' => $this->company,
        'project' => $this->project,
        'group' => $this->group,
        'task' => $this->task,
    ] = createProjectWithTask();

    Storage::fake('attachments');
    Storage::fake('public');
});

it('uploads and deletes an attachment without using the public disk', function () {
    Event::fake([AttachmentsUploaded::class, AttachmentDeleted::class]);

    $response = $this->postJson(route('projects.tasks.attachments.upload', [$this->project, $this->task]), [
        'attachments' => [
            UploadedFile::fake()->createWithContent('spec.php.pdf', '%PDF-1.4 test'),
        ],
    ])->assertOk()
        ->assertJsonPath('files.0.name', 'spec.php.pdf');

    $attachment = Attachment::find($response->json('files.0.id'));

    expect($attachment)->not->toBeNull()
        ->and($attachment->task_id)->toBe($this->task->id)
        ->and($attachment->path)->toStartWith("tasks/{$this->task->id}/")
        ->and($attachment->path)->toEndWith('.pdf')
        ->and($attachment->path)->not->toContain('.php')
        ->and($attachment->path)->not->toStartWith('/storage/')
        ->and($response->json('files.0.url'))->toContain('/task-attachments/')
        ->and($response->json('files.0.url'))->toContain('/download')
        ->and($response->json('files.0.url'))->not->toContain('/storage/');

    Storage::disk('attachments')->assertExists($attachment->path);
    Storage::disk('public')->assertMissing($attachment->path);
    expect(Storage::disk('attachments')->get($attachment->path))->toBe('%PDF-1.4 test');

    $download = $this->get(route('task-attachments.download', $attachment))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('x-content-type-options', 'nosniff')
        ->assertHeader('content-security-policy', "default-src 'none'; sandbox");

    expect($download->headers->get('content-disposition'))->toContain('attachment')
        ->and($download->headers->get('content-disposition'))->toContain('spec.php.pdf')
        ->and($download->streamedContent())->toBe('%PDF-1.4 test');

    $this->get('/storage/'.$attachment->path)->assertNotFound();

    $this->app['auth']->forgetGuards();

    $this->get(route('task-attachments.download', $attachment))
        ->assertRedirect(route('auth.login.form'));

    $this->actingAs($this->admin)
        ->deleteJson(route('projects.tasks.attachments.destroy', [$this->project, $this->task, $attachment]))
        ->assertOk();

    expect(Attachment::find($attachment->id))->toBeNull();
    Storage::disk('attachments')->assertMissing($attachment->path);
    Event::assertDispatched(AttachmentsUploaded::class);
    Event::assertDispatched(AttachmentDeleted::class);
});

it('stores an image under the detected extension and serves the thumbnail inline', function () {
    $response = $this->postJson(route('projects.tasks.attachments.upload', [$this->project, $this->task]), [
        'attachments' => [
            UploadedFile::fake()->image('photo.php.jpg'),
        ],
    ])->assertOk();

    $attachment = Attachment::find($response->json('files.0.id'));

    expect($attachment->path)->toEndWith('.jpg')
        ->and($attachment->path)->not->toContain('.php')
        ->and($attachment->name)->toBe('photo.php.jpg')
        ->and($attachment->thumb)->toStartWith("tasks/{$this->task->id}/thumbs/")
        ->and($attachment->thumb)->toEndWith('.jpg')
        ->and($response->json('files.0.thumb_url'))->toContain('/thumb');

    Storage::disk('attachments')->assertExists($attachment->path);
    Storage::disk('attachments')->assertExists($attachment->thumb);
    Storage::disk('public')->assertMissing($attachment->path);
    Storage::disk('public')->assertMissing($attachment->thumb);

    $thumb = $this->get(route('task-attachments.thumb', $attachment))
        ->assertOk()
        ->assertHeader('content-type', 'image/jpeg')
        ->assertHeader('x-content-type-options', 'nosniff');

    expect($thumb->headers->get('content-disposition'))->toContain('inline');
});

it('rejects php and html uploads on the attachment endpoint', function () {
    foreach (['x.php', 'x.html', 'notes.txt'] as $name) {
        $mime = $name === 'notes.txt' ? 'text/html' : null;

        $this->postJson(route('projects.tasks.attachments.upload', [$this->project, $this->task]), [
            'attachments' => [
                UploadedFile::fake()->create($name, 1, $mime),
            ],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('attachments.0');
    }

    expect(Storage::disk('attachments')->allFiles())->toBe([])
        ->and(Storage::disk('public')->allFiles())->toBe([])
        ->and(Attachment::count())->toBe(0);
});

it('rejects php and html uploads when creating a task', function () {
    foreach (['x.php', 'x.html'] as $name) {
        $this->postJson(route('projects.tasks.store', $this->project), attachmentTaskPayload([
            'name' => 'Blocked '.$name,
            'attachments' => [
                UploadedFile::fake()->create($name, 1),
            ],
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors('attachments.0');

        expect(Task::where('name', 'Blocked '.$name)->exists())->toBeFalse();
    }

    expect(Storage::disk('attachments')->allFiles())->toBe([])
        ->and(Storage::disk('public')->allFiles())->toBe([]);
});

it('does not keep an executable client extension when the contents sniff as another type', function () {
    $phpNamedZip = UploadedFile::fake()->create('shell.php', 10, 'application/zip');
    $officeZip = UploadedFile::fake()->create('brief.docx', 10, 'application/zip');

    expect(app(TaskAttachmentService::class)->storedExtension($phpNamedZip))->toBe('zip')
        ->and(app(TaskAttachmentService::class)->storedExtension($officeZip))->toBe('docx');

    $htmlNamedText = UploadedFile::fake()->create('notes.txt', 1, 'text/html');

    expect(fn () => app(TaskAttachmentService::class)->storedExtension($htmlNamedText))
        ->toThrow(ValidationException::class);
});

it('lets a client download an attachment on their project only', function () {
    $client = User::factory()->create();
    $client->assignRole('client');
    $client->clientCompanies()->attach($this->company);
    resetPermissionServiceCache();

    $upload = $this->postJson(route('projects.tasks.attachments.upload', [$this->project, $this->task]), [
        'attachments' => [
            UploadedFile::fake()->createWithContent('notes.txt', 'hello from the project'),
        ],
    ])->assertOk();

    $attachment = Attachment::find($upload->json('files.0.id'));

    $download = $this->actingAs($client)
        ->get(route('task-attachments.download', $attachment))
        ->assertOk()
        ->assertHeader('x-content-type-options', 'nosniff');

    expect($download->headers->get('content-type'))->toStartWith('text/plain');

    expect($download->headers->get('content-disposition'))->toContain('attachment')
        ->and($download->streamedContent())->toBe('hello from the project');

    $otherCompany = ClientCompany::create(['name' => 'Other Co']);
    $otherProject = Project::create([
        'client_company_id' => $otherCompany->id,
        'name' => 'Other',
    ]);
    $otherGroup = TaskGroup::create([
        'project_id' => $otherProject->id,
        'name' => 'To Do',
    ]);
    $otherTask = Task::create([
        'project_id' => $otherProject->id,
        'group_id' => $otherGroup->id,
        'created_by_user_id' => $this->admin->id,
        'name' => 'Secret',
        'number' => 1,
        'hidden_from_clients' => false,
        'billable' => true,
    ]);

    $secret = $this->actingAs($this->admin)
        ->postJson(route('projects.tasks.attachments.upload', [$otherProject, $otherTask]), [
            'attachments' => [
                UploadedFile::fake()->createWithContent('secret.txt', 'hidden'),
            ],
        ])->assertOk();

    $this->actingAs($client)
        ->getJson(route('task-attachments.download', $secret->json('files.0.id')))
        ->assertForbidden();

    $this->task->update(['hidden_from_clients' => true]);

    $this->actingAs($client)
        ->get(route('task-attachments.download', $attachment))
        ->assertNotFound();
});

it('moves legacy public attachments onto the private disk', function () {
    $relative = "tasks/{$this->task->id}/shell.php";
    $thumb = "tasks/{$this->task->id}/thumbs/shell.php";

    Storage::disk('public')->put($relative, '<?php echo 1;');
    Storage::disk('public')->put($thumb, 'thumb');

    $attachment = Attachment::create([
        'task_id' => $this->task->id,
        'user_id' => $this->admin->id,
        'name' => 'shell.php',
        'path' => '/storage/'.$relative,
        'thumb' => '/storage/'.$thumb,
        'type' => 'application/x-php',
        'size' => 13,
    ]);

    app(TaskAttachmentService::class)->moveLegacyPublicFiles();

    $attachment->refresh();

    expect($attachment->path)->toBe($relative)
        ->and($attachment->thumb)->toBe($thumb);

    Storage::disk('attachments')->assertExists($relative);
    Storage::disk('public')->assertMissing($relative);
    Storage::disk('attachments')->assertExists($thumb);
    Storage::disk('public')->assertMissing($thumb);
    expect(Storage::disk('attachments')->get($relative))->toBe('<?php echo 1;');

    $download = $this->get(route('task-attachments.download', $attachment))
        ->assertOk()
        ->assertHeader('content-type', 'application/octet-stream')
        ->assertHeader('x-content-type-options', 'nosniff');

    expect($download->headers->get('content-disposition'))->toContain('attachment')
        ->and($download->headers->get('content-disposition'))->toContain('shell.php')
        ->and($download->streamedContent())->toBe('<?php echo 1;');

    $this->get(route('task-attachments.thumb', $attachment))->assertNotFound();
    $this->get('/storage/'.$relative)->assertNotFound();

    Storage::disk('public')->put('tasks/99/orphan.php', '<?php echo 1;');

    app(TaskAttachmentService::class)->moveLegacyPublicFiles();

    Storage::disk('public')->assertMissing('tasks/99/orphan.php');
    Storage::disk('attachments')->assertExists('tasks/99/orphan.php');
    expect(Storage::disk('attachments')->get('tasks/99/orphan.php'))->toBe('<?php echo 1;');
});

function attachmentTaskPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Write copy',
        'group_id' => test()->group->id,
        'assigned_to_user_id' => null,
        'description' => 'Homepage hero',
        'due_on' => null,
        'estimation' => null,
        'priority_id' => null,
        'pricing_type' => 'hourly',
        'fixed_price' => null,
        'hidden_from_clients' => false,
        'billable' => true,
        'subscribed_users' => [],
        'labels' => [],
        'attachments' => [],
    ], $overrides);
}
