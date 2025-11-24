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
            $table
                ->integer('number_of_restaurant_menu_items')
                ->default(5)
                ->nullable()
                ->change();

            $table
                ->integer('number_of_product_catalogue_items')
                ->default(5)
                ->nullable()
                ->change();
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
            $table
                ->integer('number_of_restaurant_menu_items')
                ->unsigned()
                ->default(5)
                ->nullable()
                ->change();

            $table
                ->integer('number_of_product_catalogue_items')
                ->unsigned()
                ->default(5)
                ->nullable()
                ->change();
        });
    }
};
