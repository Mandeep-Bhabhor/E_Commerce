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
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();

            // User who made inquiry
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // Product for which inquiry is made
            $table->foreignId('product_id')
                ->constrained()
                ->onDelete('cascade');

            // Inquiry subject/title
            $table->string('inquiry');

            // Full message/details
            $table->text('message');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
