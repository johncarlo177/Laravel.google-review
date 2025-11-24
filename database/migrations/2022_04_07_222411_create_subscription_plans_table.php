<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('monthly_price')->default(0)->index();
            $table->boolean('is_popular')->default(false)->index();
            $table->boolean('is_trial')->default(false)->index();
            $table->boolean('is_hidden')->default(false)->index();
            $table->integer('number_of_dynamic_qrcodes')->index();
            $table->integer('number_of_scans')->index();
            $table->string('paypal_plan_id')->nullable()->index();
            $table->string('paypal_product_id')->nullable()->index();
            $table->integer('trial_days')->unsigned()->nullable();
            $table->timestamp('deleted_at')->nullable()->index();;
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
        Schema::dropIfExists('subscription_plans');
    }
};
