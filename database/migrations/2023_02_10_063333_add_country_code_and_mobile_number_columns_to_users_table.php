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
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile_iso_code')->nullable()->index();
            $table->string('mobile_number')->nullable()->index();
            $table->string('mobile_calling_code')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['mobile_iso_code']);
            $table->dropIndex(['mobile_number']);
            $table->dropIndex(['mobile_calling_code']);


            $table->dropColumn('mobile_iso_code');
            $table->dropColumn('mobile_number');
            $table->dropColumn('mobile_calling_code');
        });
    }
};
