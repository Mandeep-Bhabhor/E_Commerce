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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
             // GST, VAT, Service Tax etc
        $table->string('name');

        // percentage only (real-world taxes are percentage)
        $table->decimal('rate', 5, 2); // ex: 18.00

        // optional scope
       // $table->string('type')->default('global');
        // global / product / category (future use)

        $table->boolean('is_active')->default(true);

        $table->date('start_date')->nullable();
        $table->date('end_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
