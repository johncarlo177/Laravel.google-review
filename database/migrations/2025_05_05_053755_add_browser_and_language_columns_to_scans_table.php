<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qrcode_scans', function (Blueprint $table) {
            //
            $table->string('browser')->nullable()->index();
            $table->string('language')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qrcode_scans', function (Blueprint $table) {
            //
            $table->dropIndex(['browser', 'language']);

            $table->dropColumn('browser');
            $table->dropColumn('language');
        });
    }
};
