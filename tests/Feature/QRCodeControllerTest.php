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
class QRCodeControllerTest extends TestCase
{
    public function test_qrcode_list()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $response = $this
            ->actingAs($user)
            ->get('/api/qrcodes');

        $response->assertStatus(403);

        $permission = Permission::where('slug', 'qrcode.list')->first();

        $role->permissions()->save($permission);

        $user->refresh();

        $response = $this->actingAs($user)->get('/api/qrcodes');

        $response->assertStatus(200);

        $response->assertJsonPath('total', 0);

        $role->permissions()->save(Permission::where('slug', 'qrcode.list-all')->first());

        $user->refresh();

        $response = $this->actingAs($user)->get('/api/qrcodes');

        $response->assertJsonPath('total', QRCode::where('archived', false)->count());

        $role->permissions()->detach(Permission::where('slug', 'qrcode.list-all')->first()->id);

        $user->refresh();

        QRCode::factory(1)->state([
            'user_id' => $user->id,
        ])->create();

        $response = $this->actingAs($user)->get('/api/qrcodes');

        $response->assertJsonPath('total', 1);
    }

    public function test_store_qrcode()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $response = $this->actingAs($user)->post('/api/qrcodes');

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('qrcode.store'));

        $user->refresh();

        $response = $this->actingAs($user)->post('/api/qrcodes');

        $response->assertStatus(422);

        $qrcode = QRCode::factory()->state(['user_id' => $user->id])->make()->toArray();

        $data = json_decode(json_encode($qrcode), true);

        $response = $this->actingAs($user)->post(
            '/api/qrcodes',
            $data
        );

        $response->assertStatus(201);
    }

    public function test_update_qrcode()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $qrcode = QRCode::first();

        $response = $this->actingAs($user)->put('/api/qrcodes/' . $qrcode->id);

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('qrcode.update'));

        $user->refresh();

        $qrcode = QRCode::factory()->state(['user_id' => $user->id])->create();

        $qrcode->name = 'updated';

        $data = json_decode(json_encode($qrcode), true);

        $response = $this->actingAs($user)->put(
            '/api/qrcodes/' . $qrcode->id,
            $data
        );

        $response->assertStatus(200);

        $response->assertJsonPath('name', 'updated');

        $qrcode = QRCode::where('user_id', '<>', $user->id)->first();

        $now = Carbon::now()->format('Y-m-d:H:i:s');

        $qrcode->name = $now;

        $data = json_decode(json_encode($qrcode), true);

        $response = $this->actingAs($user)->put(
            '/api/qrcodes/' . $qrcode->id,
            $data
        );

        $response->assertStatus(403);

        $role->permissions()->save($this->permission(
            'qrcode.update-any'
        ));

        $user->refresh();

        $response = $this->actingAs($user)->put(
            '/api/qrcodes/' . $qrcode->id,
            $data
        );

        $response->assertStatus(200);

        $response->assertJsonPath('name', $now);
    }

    public function test_show_qrcode()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $qrcode = QRCode::first();

        $response = $this->actingAs($user)->get('/api/qrcodes/' . $qrcode->id);

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('qrcode.show'));

        $user->refresh();

        $qrcode = QRCode::factory()->state(['user_id' => $user->id])->create();

        $response = $this->actingAs($user)->get(
            '/api/qrcodes/' . $qrcode->id
        );

        $response->assertStatus(200);

        $qrcode = QRCode::where('user_id', '<>', $user->id)->first();

        $response = $this->actingAs($user)->get(
            '/api/qrcodes/' . $qrcode->id
        );

        $response->assertStatus(403);

        $role->permissions()->save($this->permission(
            'qrcode.show-any'
        ));

        $user->refresh();

        $response = $this->actingAs($user)->get(
            '/api/qrcodes/' . $qrcode->id
        );

        $response->assertStatus(200);
    }

    public function test_archive_qrcode()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $qrcode = QRCode::first();

        $response = $this->actingAs($user)->post('/api/qrcodes/archive/' . $qrcode->id);

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('qrcode.archive'));

        $user->refresh();

        $qrcode = QRCode::factory()->state(['user_id' => $user->id])->create();

        $response = $this->actingAs($user)->post(
            '/api/qrcodes/archive/' . $qrcode->id,
            [
                'archived' => 'true'
            ]
        );

        $response->assertStatus(200);

        $qrcode = QRCode::where('user_id', '<>', $user->id)->first();

        $response = $this->actingAs($user)->post(
            '/api/qrcodes/archive/' . $qrcode->id
        );

        $response->assertStatus(403);

        $role->permissions()->save($this->permission(
            'qrcode.archive-any'
        ));

        $user->refresh();

        $response = $this->actingAs($user)->post(
            '/api/qrcodes/archive/' . $qrcode->id,
            [
                'archived' => 'true'
            ]
        );

        $response->assertStatus(200);
    }

    public function test_show_stats()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $qrcode = QRCode::first();

        $response = $this->actingAs($user)->get('/api/qrcodes/stats/' . $qrcode->id);

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('qrcode.showStats'));

        $user->refresh();

        $qrcode = QRCode::factory()->state(['user_id' => $user->id])->create();

        $response = $this->actingAs($user)->get(
            '/api/qrcodes/stats/' . $qrcode->id
        );

        $response->assertStatus(200);

        $qrcode = QRCode::where('user_id', '<>', $user->id)->first();

        $response = $this->actingAs($user)->get(
            '/api/qrcodes/stats/' . $qrcode->id
        );

        $response->assertStatus(403);

        $role->permissions()->save($this->permission(
            'qrcode.showStats-any'
        ));

        $user->refresh();

        $response = $this->actingAs($user)->get(
            '/api/qrcodes/stats/' . $qrcode->id
        );

        $response->assertStatus(200);
    }
}
