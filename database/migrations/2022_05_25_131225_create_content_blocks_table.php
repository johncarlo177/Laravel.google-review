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
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('position')->index();
            $table->string('title')->nullable()->index();
            $table->longText('content')->nullable();

            $table->integer('sort_order')->unsigned()->default(0);

            // Appearance parameters
            $table->string('title_color')->nullable();
            $table->string('background_color')->nullable();
            $table->string('content_color')->nullable();
            $table->string('title_alignment')->nullable();
            $table->string('content_alignment')->nullable();

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
        Schema::dropIfExists('content_blocks');
    }
};
