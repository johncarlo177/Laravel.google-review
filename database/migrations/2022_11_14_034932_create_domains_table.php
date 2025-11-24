<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();

            $table->string('host')->unique();

            $table->string('protocol')->index();

            $table->foreignIdFor(User::class)->constrained();

            $table->string('availability')->default('private')->index();

            $table->string('status')->default('draft')->index();

            $table->integer('sort_order')->unsigned()->default(0)->index();

            $table->boolean('is_default')->default(false)->index();

            $table->boolean('readonly')->default(false)->index();

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
        Schema::dropIfExists('domains');
    }
};
