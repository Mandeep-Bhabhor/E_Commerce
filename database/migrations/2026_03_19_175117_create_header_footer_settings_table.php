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
        Schema::create('header_footer_settings', function (Blueprint $table) {
            $table->id();
            $table->json('header_logo')->nullable();
            $table->json('footer_logo')->nullable();
            $table->json('email')->nullable();
            $table->json('phone')->nullable();
            $table->boolean('show_header_logo')->default(true);
            $table->boolean('show_footer_logo')->default(true);
            $table->boolean('show_email')->default(true);
            $table->boolean('show_phone')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('header_footer_settings');
    }
};
