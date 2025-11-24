<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;



use App\Models\PaypalPaymentGateway;
use App\Models\Transaction;
use App\Support\PaymentProcessors\PayPalPaymentProcessor;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'source' => (new PayPalPaymentProcessor)->slug(),
            'currency' => 'USD',
            'status' => $this->faker->randomDigit() % 2 === 0 ? Transaction::STATUS_SUCCESS : Transaction::STATUS_FAILED,
        ];
    }
}
