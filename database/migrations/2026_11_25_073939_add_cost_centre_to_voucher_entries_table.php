<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('voucher_entries', function (Blueprint $table) {
            $table->string('cost_centre_tally_guid')->nullable()->after('ledger_tally_guid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voucher_entries', function (Blueprint $table) {
            $table->dropColumn('cost_centre_tally_guid');
        });
    }
};
