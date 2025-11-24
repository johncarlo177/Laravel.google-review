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
        Schema::table('domains', function (Blueprint $table) {
            $table->bigInteger('home_page_qrcode_id')->unsigned()->nullable();

            $table->foreign('home_page_qrcode_id')
                ->references('id')
                ->on('qrcodes')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropForeign(['home_page_qrcode_id']);
            $table->dropColumn('home_page_qrcode_id');
        });
    }
};
