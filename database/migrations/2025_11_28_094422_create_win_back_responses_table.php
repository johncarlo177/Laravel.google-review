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
        Schema::create('win_back_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('win_back_messages')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('win_back_customers')->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained('win_back_campaigns')->cascadeOnDelete();
            $table->enum('type', ['reply', 'click', 'visit', 'booking', 'purchase', 'unsubscribe', 'ignore'])->default('reply');
            $table->text('content')->nullable(); // Reply text or action description
            $table->string('channel')->nullable(); // Where response came from
            $table->boolean('is_positive')->default(true);
            $table->boolean('notified_business')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->json('metadata')->nullable(); // Additional response data
            $table->timestamps();
            
            $table->index(['campaign_id', 'type']);
            $table->index(['customer_id', 'created_at']);
            $table->index('notified_business');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('win_back_responses');
    }
};
