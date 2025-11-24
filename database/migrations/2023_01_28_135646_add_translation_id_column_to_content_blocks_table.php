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
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->bigInteger('translation_id')
                ->unsigned()
                ->nullable();

            $table
                ->foreign('translation_id')
                ->references('id')
                ->on('translations')
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
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->dropForeign('content_blocks_translation_id_foreign');
            $table->dropColumn('translation_id');
        });
    }
};
