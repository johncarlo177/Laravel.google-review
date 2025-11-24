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
        Schema::create('lead_forms', function (Blueprint $table) {
            $table->id();

            $table->string('related_model')
                ->index()
                ->nullable();

            $table->bigInteger('related_model_id')
                ->unsigned()
                ->index()
                ->nullable();

            $table->longText('fields')->nullable();

            $table->longText('configs')->nullable();

            $table
                ->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();

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
        Schema::dropIfExists('lead_forms');
    }
};
