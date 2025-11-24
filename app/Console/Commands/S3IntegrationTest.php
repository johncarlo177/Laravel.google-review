<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class S3IntegrationTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 's3:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'S3 Integration Test';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = 'folder/sample-file.txt';

        $fs = Storage::disk('s3');

        $fs->put($path, 'hello this is sample text');

        if ($fs->exists($path)) {
            $this->line('File exists');
        } else {
            $this->line('File do not exists');
        }

        return 0;
    }
}
