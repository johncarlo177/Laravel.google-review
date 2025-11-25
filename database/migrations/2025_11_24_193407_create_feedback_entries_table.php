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
        Schema::create('feedback_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qrcode_id')->nullable()->constrained()->cascadeOnDelete(); // business_id equivalent
            $table->integer('rating'); // 1-5 stars
            $table->text('comment')->nullable();
            $table->string('contact')->nullable(); // email or phone
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->nullable();
            $table->enum('urgency', ['low', 'medium', 'high'])->nullable();
            $table->enum('category', ['billing', 'service', 'staff', 'product', 'scheduling', 'other'])->nullable();
            $table->boolean('escalate')->default(false);
            $table->text('gpt_reply')->nullable();
            $table->text('gpt_next_step')->nullable();
            $table->json('gpt_actions')->nullable(); // 3 recommended actions
            $table->enum('status', ['new', 'resolved', 'escalated'])->default('new');
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
        Schema::dropIfExists('feedback_entries');
    }
};
