<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tally_company_id')->constrained()->cascadeOnDelete();
            $table->string('tally_guid');
            $table->string('voucher_type');
            $table->string('voucher_number')->nullable(); // display only — never the uniqueness key, brief §12
            $table->date('voucher_date');
            $table->text('narration')->nullable();
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->timestamps();

            $table->unique(['tally_company_id', 'tally_guid']);
            $table->index(['tally_company_id', 'voucher_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
