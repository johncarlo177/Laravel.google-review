<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Database\Factories\SubscriptionPlanFactory;

class SubscriptionPlanSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $factory = new SubscriptionPlanFactory;

        $factory->pro()->create();
        $factory->lite()->create();
        $factory->trial()->create();
        $factory->starter()->create();
    }
}
