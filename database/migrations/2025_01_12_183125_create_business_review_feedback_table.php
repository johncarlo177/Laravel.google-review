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
        Schema::create(
            'business_review_feedback',

            function (Blueprint $table) {
                $table->id();

                $table->integer('stars')->nullable();

                $table->string('name')->nullable();

                $table->string('email')->nullable();

                $table->string('mobile')->nullable();

                $table->longText('feedback')->nullable();

                $table->foreignId('qrcode_id')
                    ->nullable()
                    ->constrained()
                    ->cascadeOnDelete();

                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_review_feedback');
    }
};
