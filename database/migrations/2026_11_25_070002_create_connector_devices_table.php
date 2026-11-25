<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connector_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('device_name');
            $table->enum('status', [
                'awaiting_pairing',
                'connected',
                'tally_not_running',
                'tally_service_unavailable',
                'no_company_loaded',
                'company_not_authorized',
                'internet_disconnected',
                'synchronizing',
                'sync_failed',
                'authentication_expired',
                'device_revoked',
            ])->default('awaiting_pairing');
            $table->string('access_token_hash')->nullable();
            $table->string('refresh_token_hash')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('connector_version')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connector_devices');
    }
};
