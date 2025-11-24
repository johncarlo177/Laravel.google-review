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
        Schema::table('template_categories', function (Blueprint $table) {
            $table->integer('sort_order')->nullable()->default(1000)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('template_categories', function (Blueprint $table) {
            $table->dropIndex(['sort_order']);

            $table->dropColumn('sort_order');
        });
    }
};
