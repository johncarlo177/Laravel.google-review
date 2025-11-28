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
        Schema::create('win_back_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // lost, dormant, vip, one-time, failed-lead
            $table->string('display_name'); // "Lost Customers", "Dormant Customers", etc.
            $table->integer('days_threshold')->nullable(); // 60 for lost, 30 for dormant
            $table->text('default_message_template')->nullable(); // AI-generated template
            $table->json('message_tone')->nullable(); // friendly, professional, casual
            $table->string('preferred_channel')->nullable(); // sms, email, whatsapp
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('win_back_segments');
    }
};
