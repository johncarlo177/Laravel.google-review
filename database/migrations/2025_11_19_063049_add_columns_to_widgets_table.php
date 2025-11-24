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
        Schema::table('widgets', function (Blueprint $table) {
            //

            $table->string('widget_hover_background_color')->nullable();

            $table->string('widget_hover_text_color')->nullable();

            $table->string('widget_position')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('widgets', function (Blueprint $table) {
            //
            $table->dropColumn('widget_hover_background_color');

            $table->dropColumn('widget_hover_text_color');

            $table->dropColumn('widget_position');
        });
    }
};
