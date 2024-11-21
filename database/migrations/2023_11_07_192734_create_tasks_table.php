<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id');
            $table->foreignId('group_id');
            $table->foreignId('created_by_user_id')->nullable();
            $table->foreignId('assigned_to_user_id')->nullable();
            $table->foreignId('invoice_id')->nullable();
            $table->string('name');
            $table->unsignedInteger('number');
            $table->text('description')->nullable();
            $table->date('due_on')->nullable();
            $table->decimal('estimation', 6, 2)->unsigned()->nullable();
            $table->boolean('hidden_from_clients')->default(false);
            $table->boolean('billable')->default(true);
            $table->unsignedInteger('order_column');
            $table->timestamps();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->archivedAt();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
