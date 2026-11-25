<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connector_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connector_device_id')->constrained()->cascadeOnDelete();
            $table->enum('level', ['debug', 'info', 'warning', 'error']);
            $table->string('message');
            $table->json('context')->nullable(); // redacted before insert — see brief §18
            $table->timestamp('created_at')->nullable();

            $table->index(['connector_device_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connector_logs');
    }
};
