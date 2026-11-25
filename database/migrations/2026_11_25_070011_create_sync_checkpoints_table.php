<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tally_company_id')->constrained()->cascadeOnDelete();
            $table->string('data_module');
            $table->string('last_synced_marker')->nullable(); // last voucher date / alter-id seen
            $table->timestamps();

            $table->unique(['tally_company_id', 'data_module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_checkpoints');
    }
};
