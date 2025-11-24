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
        Schema::create('quick_qr_art_predictions', function (Blueprint $table) {
            $table->id();

            $table->string('api_id')->unique();

            $table
                ->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('status')->index();

            $table->string('output')->nullable();

            $table->longText('input')->nullable();

            $table
                ->foreignId('qrcode_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table
                ->foreignId('output_file_id')
                ->nullable()
                ->constrained('files')
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
        Schema::dropIfExists('quick_qr_art_predictions');
    }
};
