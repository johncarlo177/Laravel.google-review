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
        if (Schema::hasColumn('invoices', 'billing_details_response_id')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            //
            $table->bigInteger('billing_details_response_id')
                ->nullable()
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            //
            $table->dropIndex(['billing_details_response_id']);

            $table->dropColumn('billing_details_response_id');
        });
    }
};
