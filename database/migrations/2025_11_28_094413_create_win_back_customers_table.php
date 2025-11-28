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
        Schema::create('win_back_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Business owner
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('last_visit_date')->nullable();
            $table->decimal('total_spend', 10, 2)->default(0);
            $table->integer('visit_count')->default(0);
            $table->decimal('lifetime_value', 10, 2)->default(0);
            $table->string('customer_type')->nullable(); // vip, regular, one-time, etc.
            $table->integer('days_since_last_visit')->nullable();
            $table->string('segment')->nullable(); // lost, dormant, vip, one-time, failed-lead
            $table->json('metadata')->nullable(); // Additional data from import
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'segment']);
            $table->index(['user_id', 'days_since_last_visit']);
            $table->index('last_visit_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('win_back_customers');
    }
};
