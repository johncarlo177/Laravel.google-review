<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

use App\Models\QRCode;
use App\Models\User;
use Database\Factories\QRCodeFactory;
use Illuminate\Database\Eloquent\Factories\Sequence;

class QRCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!env('DEV_SEED_QRCODES')) return;

        QRCode::factory(1)
            ->state(
                new Sequence(
                    fn ($sequence) => array_merge(
                        [
                            'user_id' => 1
                        ],
                        QRCodeFactory::palette($sequence->index)
                    )
                )
            )->create();
    }
}
