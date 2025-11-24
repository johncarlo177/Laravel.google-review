<?php

namespace App\Policies;

use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class PaymentGatewayPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function list(User $user)
    {
        return $user->permitted('payment-gateway.list-all');
    }

    public function update(User $user, PaymentGateway $paymentGateway)
    {
        $this->restrictDemo();

        return $user->permitted('payment-gateway.update-any');
    }

    public function show(User $user, PaymentGateway $paymentGateway)
    {

        return $user->permitted('payment-gateway.show-any');
    }
}
