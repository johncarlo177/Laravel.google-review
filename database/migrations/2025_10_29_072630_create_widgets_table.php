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
            'widgets',
            function (Blueprint $table) {
                // 
                $table->id();

                $table->uuid()->nullable()->index();

                $table->string('name')->nullable()->index();

                $table->foreignId('qrcode_id')
                    ->nullable()
                    ->constrained('qrcodes')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();

                $table->string('widget_background_color')->nullable();

                $table->string('widget_text_color')->nullable();

                $table->foreignId('widget_icon_id')->nullable();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();

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
        Schema::dropIfExists('widgets');
    }
};
