<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('voucher_id')->constrained()->cascadeOnDelete();
            $table->string('ledger_tally_guid');
            $table->decimal('amount', 18, 2);
            $table->boolean('is_debit');
            $table->timestamps();

            $table->index(['voucher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_entries');
    }
};
