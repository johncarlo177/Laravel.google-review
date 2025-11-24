<?php

use App\Models\User;
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
        Schema::table('transactions', function (Blueprint $table) {

            $this->stampedUserId('approved', $table);

            $this->stampedUserId('rejected', $table);
        });
    }

    private function stampedUserId($fieldName, $table)
    {
        $table->unsignedBigInteger(sprintf("%s_by_id", $fieldName))->nullable();

        $table
            ->foreign(sprintf("%s_by_id", $fieldName))
            ->references('id')
            ->on('users')
            ->onDelete('cascade')
            ->onUpdate('cascade');

        $table
            ->timestamp(sprintf('%s_at', $fieldName))
            ->nullable()
            ->index();
    }

    private function dropStampedUserId($fieldName, $table)
    {
        $table->dropForeign(sprintf('transactions_%s_by_id_foreign', $fieldName));
        $table->dropColumn(sprintf('%s_by_id', $fieldName));

        $table->dropIndex(sprintf('transactions_%s_at_index', $fieldName));
        $table->dropColumn(sprintf('%s_at', $fieldName));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $this->dropStampedUserId('approved', $table);
            $this->dropStampedUserId('rejected', $table);
        });
    }
};
