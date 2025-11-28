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
        Schema::create('win_back_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('win_back_campaigns')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('win_back_customers')->cascadeOnDelete();
            $table->enum('channel', ['sms', 'email', 'whatsapp', 'messenger', 'push', 'inbox'])->default('sms');
            $table->text('message_content');
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'bounced'])->default('pending');
            $table->string('external_id')->nullable(); // SMS/Email provider message ID
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('is_ai_generated')->default(true);
            $table->json('ai_metadata')->nullable(); // AI generation details
            $table->timestamps();
            
            $table->index(['campaign_id', 'status']);
            $table->index(['customer_id', 'channel']);
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('win_back_messages');
    }
};
