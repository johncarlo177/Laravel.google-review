<?php

namespace Tests\Feature;


use App\Interfaces\FileManager;
use App\Models\File;
use App\Models\QRCode;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


/**
 * @group tested
 */
class FilesControllerTest extends TestCase
{

    public function test_store()
    {
        Storage::fake('logos');

        $user = User::whereHas('qrcodes')->with('qrcodes')->get()->random();

        $data = [
            'file' => UploadedFile::fake()->createWithContent('file.sh', 'This is text file'),
            'type' => FileManager::FILE_TYPE_QRCODE_LOGO,
            'attachable_type' => QRCode::class,
            'attachable_id' => $user->qrcodes->random()->id
        ];

        $response = $this->actingAs($user)->post('/api/files', $data);

        $response->assertStatus(422);

        $data['file'] = UploadedFile::fake()->createWithContent('file.png', 'This is text file');

        $response = $this->actingAs($user)->post('/api/files', $data);

        $response->dump();

        $file = $response->json();

        Storage::disk('logos')->exists($file['path']);
    }

    public function test_show_file()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $subject = File::all()->random();

        $response = $this->actingAs($user)->get('/api/files/' . $subject->id);

        $response->assertStatus(403);

        // show plan is allowed for guests.
    }

    public function test_destroy_file()
    {
        list($user, $role) = $this->newUserWithTestRole();

        $subscriptionPlan = SubscriptionPlan::factory()->pro()->state(
            [
                'name' => 'test'
            ]
        )->create();

        $response = $this->actingAs($user)->delete('/api/subscription-plans/' . $subscriptionPlan->id);

        $response->assertStatus(403);

        $role->permissions()->save($this->permission('subscription-plan.destroy-any'));

        $user->refresh();

        $response = $this->actingAs($user)->delete('/api/subscription-plans/' . $subscriptionPlan->id);

        $response->assertStatus(200);
    }
}
