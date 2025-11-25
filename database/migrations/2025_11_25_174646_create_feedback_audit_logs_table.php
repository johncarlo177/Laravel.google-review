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
        Schema::create('feedback_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_entry_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // e.g., 'classified', 'reply_generated', 'escalated', 'resolved'
            $table->json('details')->nullable(); // Additional context
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_address')->nullable();
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
        Schema::dropIfExists('feedback_audit_logs');
    }
};
