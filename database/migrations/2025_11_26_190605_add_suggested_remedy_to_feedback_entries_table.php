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
        Schema::table('feedback_entries', function (Blueprint $table) {
            $table->string('gpt_suggested_remedy')->nullable()->after('gpt_next_step');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feedback_entries', function (Blueprint $table) {
            $table->dropColumn('gpt_suggested_remedy');
        });
    }
};
