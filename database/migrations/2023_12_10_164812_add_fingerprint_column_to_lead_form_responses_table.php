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
        Schema::table('lead_form_responses', function (Blueprint $table) {
            $table->string('fingerprint')->after('fields')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_form_responses', function (Blueprint $table) {
            $table->dropIndex(['fingerprint']);
            $table->dropColumn('fingerprint');
        });
    }
};
