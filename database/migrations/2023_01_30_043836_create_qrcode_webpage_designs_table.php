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
        Schema::create('qrcode_webpage_designs', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('qrcode_id')->unsigned();

            $table
                ->foreign('qrcode_id')
                ->references('id')
                ->on('qrcodes')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->longText('design')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qrcode_webpage_designs');
    }
};
