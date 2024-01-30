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
        Schema::create('client_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable();
            $table->foreignId('currency_id')->nullable();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('web')->nullable();
            $table->string('iban')->nullable();
            $table->string('swift')->nullable();
            $table->string('business_id')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('vat')->nullable();
            $table->timestamps();
            $table->archivedAt();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_companies');
    }
};
