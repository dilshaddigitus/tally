<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tally_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tally_installation_id')->constrained()->cascadeOnDelete();
            $table->string('tally_guid');
            $table->string('name');
            $table->date('financial_year_start')->nullable();
            $table->timestamps();

            $table->unique(['tally_installation_id', 'tally_guid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tally_companies');
    }
};
