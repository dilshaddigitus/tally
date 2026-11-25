<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tally_installations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connector_device_id')->constrained()->cascadeOnDelete();
            $table->string('detected_version')->nullable();
            $table->string('os_info')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tally_installations');
    }
};
