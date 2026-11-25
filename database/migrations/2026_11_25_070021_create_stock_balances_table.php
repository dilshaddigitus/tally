<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_item_id')->constrained()->cascadeOnDelete();
            $table->string('godown')->nullable();
            $table->decimal('quantity', 18, 3)->default(0);
            $table->decimal('rate', 18, 4)->nullable();
            $table->decimal('value', 18, 2)->nullable();
            $table->date('as_of_date');
            $table->timestamps();

            $table->index(['stock_item_id', 'as_of_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
    }
};
