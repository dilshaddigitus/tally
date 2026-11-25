<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sync_job_id')->constrained()->cascadeOnDelete();
            $table->string('error_code');
            $table->text('message');
            $table->json('context')->nullable(); // redacted
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_errors');
    }
};
