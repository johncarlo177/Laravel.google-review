<?php

use App\Support\System\Traits\WriteLogs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use WriteLogs;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        $this->logWarning('Before dropping invoices table');

        Schema::dropIfExists('invoices');

        $this->logWarning('After dropping invoices table');

        Schema::create(
            'invoices',

            function (Blueprint $table) {

                $table->id()->from(3333);

                $table->uuid()->nullable()->index();

                $table->foreignId('user_id')
                    ->nullable()
                    ->index();

                $table->decimal('tax_rate')
                    ->index()
                    ->nullable();

                $table->decimal('tax_amount')
                    ->index()
                    ->nullable();

                $table->decimal('subtotal')
                    ->index()
                    ->nullable();

                $table->decimal('total')
                    ->index()
                    ->nullable();

                $table->timestamps();
            }
        );

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('invoices');

        Schema::enableForeignKeyConstraints();
    }
};
