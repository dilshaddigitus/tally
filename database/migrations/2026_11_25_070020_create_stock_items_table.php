<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tally_company_id')->constrained()->cascadeOnDelete();
            $table->string('tally_guid');
            $table->string('name');
            $table->string('stock_group_tally_guid')->nullable();
            $table->string('unit')->nullable();
            $table->timestamps();

            $table->unique(['tally_company_id', 'tally_guid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
