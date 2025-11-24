<?php

use App\Support\System\Traits\WriteLogs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use WriteLogs;

    protected function addColumn($name)
    {
        try {
            Schema::table('invoice_items', function (Blueprint $table) use ($name) {
                //
                $table->decimal($name)
                    ->index()
                    ->nullable();
            });
        } catch (Throwable $th) {
            // 
            $this->logDebug($th->getMessage());
        }
    }

    protected function dropColumn($name)
    {
        try {
            Schema::table('invoice_items', function (Blueprint $table) use ($name) {
                //
                $table->dropIndex([$name]);
                $table->dropColumn($name);
            });
        } catch (Throwable $th) {
            // 
            $this->logDebug($th->getMessage());
        }
    }


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->addColumn('tax_rate');
        $this->addColumn('tax_amount');
        $this->addColumn('subtotal');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropColumn('tax_rate');
        $this->dropColumn('tax_amount');
        $this->dropColumn('subtotal');
    }
};
