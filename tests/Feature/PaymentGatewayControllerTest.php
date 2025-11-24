<?php

namespace Tests\Feature;

use App\Models\PaymentGateway;
use App\Models\PaypalPaymentGateway;

/**
 * @group tested
 */
class PaymentGatewayControllerTest extends TestCase
{
    public function test_payment_gateway_list()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $response = $this
            ->actingAs($user)
            ->get('/api/payment-gateways');

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('payment-gateway.list-all'));

        $user->refresh();

        $response = $this->actingAs($user)->get('/api/payment-gateways');

        $response->assertJsonPath('total', PaymentGateway::count());
    }

    public function test_update_payment_gateway()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $paymentGateway = $this->makePaymentGateway();

        $response = $this->actingAs($user)->put('/api/payment-gateways/' . $paymentGateway->id);

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('payment-gateway.update-any'));

        $user->refresh();

        $response = $this->actingAs($user)->put('/api/payment-gateways/' . $paymentGateway->id);

        $response->assertStatus(422);

        $paymentGateway->name = 'test';

        $data = json_decode(json_encode($paymentGateway), true);

        $response = $this->actingAs($user)->put(
            '/api/payment-gateways/' . $paymentGateway->id,
            $data
        );

        $response->assertStatus(200);

        $response->assertJsonPath('name', 'test');

        $paymentGateway->delete();
    }

    public function test_show_payment_gateway()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $paymentGateway = $this->makePaymentGateway();

        $response = $this->actingAs($user)->get('/api/payment-gateways/' . $paymentGateway->id);

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('payment-gateway.show-any'));

        $user->refresh();

        $response = $this->actingAs($user)->get('/api/payment-gateways/' . $paymentGateway->id);

        $response->assertStatus(200);

        $paymentGateway->delete();
    }

    private function makePaymentGateway()
    {
        $gateway = new PaymentGateway;

        $gateway->name = 'Test gateway';

        $gateway->slug = 'test';

        $gateway->enabled = true;

        $gateway->mode = PaypalPaymentGateway::MODE_SANDBOX;

        $gateway->save();

        return $gateway;
    }
}
