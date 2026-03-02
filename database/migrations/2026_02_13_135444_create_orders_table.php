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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('address_id')->constrained()->cascadeOnDelete();

            $table->decimal('grand_total', 10, 2);
            // $table->decimal('shipping_charge', 10, 2)->default(0);

            //   $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');

            $table->enum('order_status', [
                'placed',
                // 'processing',
                // 'shipped',
                'delivered',
                'cancelled',
            ])->default('placed');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
