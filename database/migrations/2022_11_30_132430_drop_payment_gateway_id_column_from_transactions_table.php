<?php

use App\Models\PaymentGateway;
use App\Models\Transaction;
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
        $this->copyTransactionsPaymentGatewaysToSourceField();

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign('transactions_payment_gateway_id_foreign');
            $table->dropColumn('payment_gateway_id');
        });
    }

    private function copyTransactionsPaymentGatewaysToSourceField()
    {
        Transaction::all()->each(function ($transaction) {
            $paymentGateway = PaymentGateway::find(@$transaction->payment_gateway_id);

            if ($paymentGateway) {
                $transaction->source = $paymentGateway->slug;
                $transaction->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->bigInteger('payment_gateway_id')->unsigned()->nullable();
            $table->foreign('payment_gateway_id')->references('id')->on('payment_gateways')->onDelete('set null');
        });
    }
};
