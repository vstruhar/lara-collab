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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_company_id');
            $table->unsignedBigInteger('created_by_user_id');
            $table->string('number');
            $table->string('status');
            $table->unsignedInteger('amount');
            $table->unsignedInteger('amount_with_tax')->nullable();
            $table->date('billing_date')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('created_by_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
