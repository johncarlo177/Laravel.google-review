<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\QRCode;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * @group tested
 */
class UsersControllerTest extends TestCase
{
    public function test_user_list()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $response = $this
            ->actingAs($user)
            ->get('/api/users');

        $response->assertStatus(403);

        $role->permissions()->save(Permission::where('slug', 'user.list-all')->first());

        $user->refresh();

        $response = $this->actingAs($user)->get('/api/users');

        $response->assertJsonPath('total', User::count());
    }

    public function test_store_user()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $response = $this->actingAs($user)->post('/api/users');

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('user.store'));

        $user->refresh();

        $response = $this->actingAs($user)->post('/api/users');

        $response->assertStatus(422);

        $password = 'test123456';

        $subject = User::factory()->state(fn () => [
            'password' => $password,
            'password_confirmation' => $password
        ])->make()->toArray();

        $data = json_decode(json_encode($subject), true);

        $data = compact('password') + $data;

        $response = $this->actingAs($user)->post(
            '/api/users',
            $data
        );

        $response->assertStatus(201);
    }

    public function test_update_user()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $subject = User::all()->random();

        $response = $this->actingAs($user)->put('/api/users/' . $subject->id);

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('user.update-any'));

        $user->refresh();

        $response = $this->actingAs($user)->put('/api/users/' . $subject->id);

        $response->assertStatus(422);

        $subject->name = 'test';

        $data = json_decode(json_encode($subject), true);

        $response = $this->actingAs($user)->put(
            '/api/users/' . $subject->id,
            $data
        );

        $response->assertStatus(200);

        $response->assertJsonPath('name', 'test');
    }

    public function test_show_user()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $subject = User::all()->random();

        $response = $this->actingAs($user)->get('/api/users/' . $subject->id);

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('user.show-any'));

        $user->refresh();

        $response = $this->actingAs($user)->get('/api/users/' . $subject->id);

        $response->assertStatus(200);
    }
}
