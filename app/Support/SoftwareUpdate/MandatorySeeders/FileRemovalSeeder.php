<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;

use Illuminate\Support\Facades\File;

class FileRemovalSeeder extends Seeder
{
    protected $version = '2.36';

    protected function pathsToRemove()
    {
        return array(
            [
                'path' => base_path('app/Plugins/Neting'),
                'condition' => $this->notLocal() && !preg_match('/neting/', url('/'))
            ],
            [
                'path' => base_path('app/Plugins/Manager.php'),
                'condition' => $this->notLocal(),
            ],
            [
                'path' => base_path('compose.*'),
                'condition' => $this->notLocal()
            ],
            [
                'path' => public_path('affiliates-coupons*.js'),
                'condition' => true,
            ]
        );
    }

    protected function run()
    {
        foreach ($this->pathsToRemove() as $pathInformation) {
            $condition = $pathInformation['condition'];
            $path = $pathInformation['path'];

            if (!$condition) continue;

            $path = glob($path);

            foreach ($path as $fullPath) {
                $this->recursiveRemove($fullPath);
            }
        }
    }

    private function recursiveRemove(string $path)
    {
        if (is_file($path)) {
            return unlink($path);
        }

        if (!is_dir($path)) return;

        return File::deleteDirectory($path);
    }

    private function notLocal()
    {
        return !$this->isLocal();
    }

    private function isLocal()
    {
        $files = [
            '.paddle.release.env',
        ];

        foreach ($files as $file) {
            if (file_exists(base_path($file))) {
                return true;
            }
        }

        return false;
    }
}
