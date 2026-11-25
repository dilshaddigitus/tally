<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tally_company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('connector_device_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('correlation_id')->unique();
            $table->enum('job_type', ['full', 'manual', 'scheduled', 'date_range', 'incremental', 'retry', 'reconciliation']);
            $table->string('data_module'); // company, ledgers, vouchers, inventory, reports
            $table->enum('status', ['pending', 'claimed', 'running', 'completed', 'failed'])->default('pending');
            $table->unsignedTinyInteger('attempt')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(5);
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['connector_device_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_jobs');
    }
};
