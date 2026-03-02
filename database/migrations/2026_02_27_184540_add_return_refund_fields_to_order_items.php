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
        Schema::table('orderitems', function (Blueprint $table) {
            //
            $table->timestamp('delivered_at')->nullable();

            $table->boolean('return_requested')->default(false);

            $table->timestamp('return_requested_at')->nullable();

            $table->boolean('is_returned')->default(false);

            $table->timestamp('returned_at')->nullable();

            $table->decimal('refund_amount', 10, 2)->nullable();

            $table->enum('refund_status', ['pending', 'processed', 'rejected'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orderitems', function (Blueprint $table) {
            //
            $table->dropColumn('refund_status');
            $table->dropColumn('refund_amount');
            $table->dropColumn('returned_at');
            $table->dropColumn('is_returned');
            $table->dropColumn('return_requested_at');
            $table->dropColumn('delivered_at');
            $table->dropColumn('return_requested');
        });
    }
};
