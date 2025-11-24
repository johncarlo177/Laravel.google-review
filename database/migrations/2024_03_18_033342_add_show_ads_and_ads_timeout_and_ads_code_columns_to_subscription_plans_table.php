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
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('show_ads')->nullable()->default("disabled");
            $table->integer('ads_timeout')->unsigned()->nullable()->default(15);
            $table->longText('ads_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('show_ads');
            $table->dropColumn('ads_timeout');
            $table->dropColumn('ads_code');
        });
    }
};
