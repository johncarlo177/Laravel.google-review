<?php

namespace Tests\Feature;

use App\Models\Transaction;

/**
 * @group tested
 */
class TransactionControllerTest extends TestCase
{
    public function test_transaction_list()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $response = $this
            ->actingAs($user)
            ->get('/api/transactions');

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('transaction.list-all'));

        $user->refresh();

        $response = $this->actingAs($user)->get('/api/transactions');

        $response->assertJsonPath('total', Transaction::count());
    }
}
