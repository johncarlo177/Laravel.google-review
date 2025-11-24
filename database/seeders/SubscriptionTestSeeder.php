<?php

namespace Database\Seeders;

use App\Events\SubscriptionVerified;
use App\Models\QRCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;

use App\Models\Subscription;

use App\Models\SubscriptionPlan;

use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;

class SubscriptionTestSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        return;

        $user = User::factory()->state([
            'email' => 'walidh93@gmail.com'
        ])->create();

        event(new Registered($user));

        $subscription = $user->subscriptions[0];

        $subscription->created_at = Carbon::now()->subDays(15);

        $subscription->save();

        $paidPlan = SubscriptionPlan::factory()->pro()->state([
            'number_of_dynamic_qrcodes' => 1,
            'number_of_scans' => 1
        ])->create();

        $paidSubscription = new Subscription([
            'subscription_plan_id' => $paidPlan->id,
            'user_id' => $user->id,
        ]);

        $paidSubscription->created_at = Carbon::now()->subMonth();

        $paidSubscription->save();

        sleep(1);

        event(new SubscriptionVerified($paidSubscription));

        $qrcodes = QRCode::limit(15)->get();

        foreach ($qrcodes as $qrcode) {
            $qrcode->user_id = $user->id;
            $qrcode->save();
        }
    }
}
