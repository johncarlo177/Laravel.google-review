<?php

namespace App\Console\Commands;

use App\Interfaces\FileManager;
use App\Support\SoftwareUpdate\DatabaseUpdateManager;
use App\Support\System\CacheManager;
use Illuminate\Console\Command;

use Illuminate\Support\Facades\Storage;

class AppRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rolls back all migrations, runs all seeders and clear generated files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        if (app()->environment('production')) {
            return $this->error('Cannot run this command in production mode.');
        }

        $this->deleteGeneratedFiles();

        $this->deleteUploadedFiles();

        $this->call('db:wipe');

        $this->call('migrate', ['--seed' => true]);

        $this->changeFilesOwnership();


        /** @var \App\Support\SoftwareUpdate\DatabaseUpdateManager */
        $databaseManager = app(DatabaseUpdateManager::class);

        $databaseManager->updateDatabaseIfNeeded();

        CacheManager::for('config')->rebuild();
        CacheManager::for('views')->rebuild();
    }

    private function changeFilesOwnership()
    {
        shell_exec('chown -R nobody:nobody /var/www/html/storage');
    }

    private function deleteGeneratedFiles()
    {
        $directory = Storage::path(config('qrcode.storage_path'));

        $pattern = "$directory/*";

        $files = glob($pattern); // get all file names

        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                unlink($file); // delete file
            }
        }
    }

    private function deleteUploadedFiles()
    {
        $directory = Storage::path(FileManager::UPLOAD_DIR);

        $pattern = "$directory/*";

        $files = glob($pattern); // get all file names

        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                unlink($file); // delete file
            }
        }
    }
}
