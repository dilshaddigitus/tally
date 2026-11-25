<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connector_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->text('release_notes')->nullable();
            $table->boolean('min_supported')->default(false);
            $table->timestamp('released_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connector_versions');
    }
};
