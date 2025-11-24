<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        if (Schema::hasTable('invoice_items')) {
            return;
        }

        Schema::create(
            'invoice_items',
            function (Blueprint $table) {
                // 
                $table->id();

                $table->string('name')->nullable()->index();

                $table->longText('description')->nullable();

                $table->foreignId('invoice_id')
                    ->nullable()
                    ->constrained()
                    ->onDelete('cascade');

                $table->decimal(
                    'unit_price'
                )
                    ->nullable()
                    ->index();

                $table->integer(
                    'quantity'
                )
                    ->unsigned()
                    ->nullable()
                    ->default(1)
                    ->index();

                $table->decimal(
                    'total'
                )
                    ->nullable()
                    ->index();

                $table->string(
                    'related_model'
                )
                    ->nullable()
                    ->index();

                $table->foreignId(
                    'related_model_id'
                )
                    ->nullable()
                    ->index();

                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_items');
    }
};
