<?php

use App\Services\TaskAttachmentService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Move task attachments off the public disk so they are no longer served as web files.
     */
    public function up(): void
    {
        app(TaskAttachmentService::class)->moveLegacyPublicFiles();
    }

    /**
     * Private files stay private.
     */
    public function down(): void
    {
        //
    }
};
