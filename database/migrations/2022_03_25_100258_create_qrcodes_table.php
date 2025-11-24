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
        Schema::create('qrcodes', function (Blueprint $table) {
            $table->id()->startingValue(130000);
            $table->string('name')->index()->nullable();
            $table->string('file_name')->index()->nullable();
            $table->string('type')->index()->nullable();
            $table->longText('data')->nullable();
            $table->longText('design')->nullable();
            $table->boolean('archived')->default(false)->index();
            $table->foreignId('user_id')->constrained();
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
        Schema::dropIfExists('qrcodes');
    }
};
