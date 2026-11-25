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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();
            $table->enum('billing_type', ['subscription', 'one_time']);
            $table->enum('status', ['active', 'past_due', 'cancelled', 'expired'])->default('active');
            $table->timestamp('started_at');
            $table->timestamp('renews_at')->nullable(); // null for one_time
            $table->timestamp('cancelled_at')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('payment_method')->nullable(); // 'manual', 'razorpay', 'stripe', ... — gateway TBD
            $table->string('external_reference')->nullable(); // gateway charge/subscription id, once one exists
            $table->timestamps();

            $table->index(['customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
