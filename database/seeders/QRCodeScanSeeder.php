<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\QRCode;
use App\Support\QRCodeScanManager;
use Exception;
use Illuminate\Support\Facades\Storage;

class QRCodeScanSeeder extends Seeder
{
    private static $agents = [];
    private QRCodeScanManager $scans;
    private \Faker\Generator $faker;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $qrcodes = QRCode::with('redirect')->whereHas('redirect')->get();

        $this->scans = new QRCodeScanManager;

        $this->faker = \Faker\Factory::create();

        foreach ($qrcodes as $qrcode) {
            foreach (range(0, 10) as $i) {
                while (!$this->createScan($qrcode)) {
                }
            }
        }
    }

    private function createScan($qrcode)
    {
        try {
            $_SERVER['HTTP_USER_AGENT'] = $this->userAgent();

            $this->scans->saveScanDetails($qrcode->id, $this->faker->ipv4(), [
                'created_at' => $this->faker->dateTimeBetween('-1 months', 'now')
            ]);

            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    private function userAgent()
    {
        if (empty($this::$agents))
            $this::$agents =  json_decode(
                file_get_contents(Storage::path('user-agents.json')),
                true
            );

        $i = array_rand($this::$agents);

        return $this::$agents[$i]['userAgent'];
    }
}
