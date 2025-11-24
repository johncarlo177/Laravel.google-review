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
        Schema::create('bulk_operation_instances', function (Blueprint $table) {
            $table->id()->startingValue(5555);

            $table->longText('data')->nullable();

            $table->string('name')->index()->nullable();

            $table->string('type')->index()->nullable();

            $table->string('status')->index()->nullable();

            $table
                ->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade')
                ->onUpdate('cascade');

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
        Schema::dropIfExists('bulk_operation_instances');
    }
};
