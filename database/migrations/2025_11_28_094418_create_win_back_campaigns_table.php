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
        Schema::create('win_back_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('segment_id')->nullable()->constrained('win_back_segments')->nullOnDelete();
            $table->string('segment_name')->nullable(); // If segment deleted, keep name
            $table->enum('status', ['draft', 'scheduled', 'active', 'paused', 'completed'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('total_customers')->default(0);
            $table->integer('messages_sent')->default(0);
            $table->integer('responses_received')->default(0);
            $table->integer('customers_returned')->default(0);
            $table->decimal('revenue_recovered', 10, 2)->default(0);
            $table->json('automation_settings')->nullable(); // 90-day automation config
            $table->boolean('is_automated')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('win_back_campaigns');
    }
};
