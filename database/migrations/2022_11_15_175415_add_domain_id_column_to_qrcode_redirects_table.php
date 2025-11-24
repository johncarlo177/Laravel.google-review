<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
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
        Schema::table('qrcode_redirects', function (Blueprint $table) {
            $table->bigInteger('domain_id')->nullable()->unsigned()->index();

            $table
                ->foreign('domain_id')
                ->references('id')
                ->on('domains')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qrcode_redirects', function (Blueprint $table) {
            $table->dropForeign('qrcode_redirects_domain_id_foreign');
            $table->dropColumn('domain_id');
        });
    }
};
