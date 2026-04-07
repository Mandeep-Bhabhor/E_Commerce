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
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('razorpay_order_id', 'gateway_order_id');
            $table->renameColumn('razorpay_payment_id', 'transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('gateway_order_id', 'razorpay_order_id');
            $table->renameColumn('transaction_id', 'razorpay_payment_id');
        });
    }
};