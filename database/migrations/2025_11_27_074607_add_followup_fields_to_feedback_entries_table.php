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
        Schema::table('feedback_entries', function (Blueprint $table) {
            $table->timestamp('followup_sent_at')->nullable()->after('status');
            $table->timestamp('followup_scheduled_at')->nullable()->after('followup_sent_at');
            $table->boolean('customer_satisfied')->nullable()->after('followup_scheduled_at');
            $table->boolean('google_review_requested')->default(false)->after('customer_satisfied');
            $table->text('operational_recommendation')->nullable()->after('google_review_requested');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feedback_entries', function (Blueprint $table) {
            $table->dropColumn([
                'followup_sent_at',
                'followup_scheduled_at',
                'customer_satisfied',
                'google_review_requested',
                'operational_recommendation',
            ]);
        });
    }
};
