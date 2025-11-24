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
        // Because we are not reverting the unique index in the down function
        try {

            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->dropUnique(['name']);
                $table->index('name');
            });
        } catch (Throwable $th) {
        }

        Schema::table('subscription_plans', function (Blueprint $table) {

            $table->dropIndex(['deleted_at']);

            $table->dropColumn('deleted_at');
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
            $table->timestamp('deleted_at')->index();
        });
    }
};
