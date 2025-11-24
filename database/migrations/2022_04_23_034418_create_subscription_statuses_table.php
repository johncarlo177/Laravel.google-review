<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\SubscriptionStatus;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_statuses', function (Blueprint $table) {

            $table->id();

            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');

            $table->string('status')->index()->default(SubscriptionStatus::STATUS_ACTIVE);

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_statuses');
    }
};
