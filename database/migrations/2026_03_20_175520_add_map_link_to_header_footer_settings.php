<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('header_footer_settings', function (Blueprint $table) {
            $table->text('map_iframe')->nullable();
        });
    }

    public function down()
    {
        Schema::table('header_footer_settings', function (Blueprint $table) {
            $table->dropColumn('map_iframe');
        });
    }
};
