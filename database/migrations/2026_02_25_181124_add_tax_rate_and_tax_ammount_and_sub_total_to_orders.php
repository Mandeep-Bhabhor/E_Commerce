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
              // subtotal before discount/tax
        $table->decimal('subtotal', 10, 2)->default(0)->after('address_id');

        // discount snapshot
        $table->decimal('discount_amount', 10, 2)->default(0)->after('subtotal');

        // tax snapshot
        $table->decimal('tax_rate', 5, 2)->default(0)->after('discount_amount'); 
        $table->decimal('tax_amount', 10, 2)->default(0)->after('tax_rate');

        // final total (after discount + tax)
        $table->decimal('grand_total', 10, 2)->default(0)->change();

        // optional future proofing
       // $table->decimal('shipping_amount', 10, 2)->default(0)->after('tax_amount');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropColumn('subtotal');
            $table->dropColumn('discount_amount');
            $table->dropColumn('tax_rate');
            $table->dropColumn('tax_amount');

        });
    }
};
