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
            //
            $table->enum('payment_method', ['cod', 'razorpay', 'paypal'])->default('cod');
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');

            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('razorpay_signature')->nullable();

            $table->timestamp('paid_at')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->dropColumn('payment_status');
            $table->dropColumn('razorpay_order_id');
            $table->dropColumn('razorpay_payment_id');
            $table->dropColumn('razorpay_signature');
            $table->dropColumn('paid_at');
        });
    }
};
