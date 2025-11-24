<?php

namespace App\Jobs;

use App\Models\BulkOperationInstance;
use App\Models\Folder;
use App\Models\QRCode;
use App\Models\User;
use App\Policies\Restriction\QRCodeRestrictor;
use App\Repositories\UserManager;
use App\Support\BulkOperation\BulkOperationManager;
use App\Support\QRCodeManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class DemoCleaner implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    use WriteLogs;

    public function clearBulkOperations()
    {
        $operations = new BulkOperationManager;

        BulkOperationInstance::get()->each(function (BulkOperationInstance $instance) use ($operations) {
            $operations->ofInstance($instance)->deleteAllQRCodes($instance);

            $instance->delete();
        });
    }

    public function clearQRCodes()
    {
        $restrictor = new QRCodeRestrictor;

        $ids = $restrictor->getRestrictedIds();

        $idsToDelete = QRCode::whereNotIn('id', $ids)->pluck('id');

        $manager = new QRCodeManager();

        $manager->deleteMany($idsToDelete->values()->all());
    }

    public function clearUsers()
    {
        $users = new UserManager();

        $ids = User::whereNotIn('id', [1, 4])->pluck('id');

        foreach ($ids as $id) {
            $users->deleteUser(User::find($id));
        }
    }

    public function handle()
    {
        if (!app()->environment('demo')) {
            return;
        }

        $this->clearUsers();

        $this->clearQRCodes();

        $this->clearBulkOperations();

        Folder::where('id', '>', 3)->delete();

        $this->logInfo('Demo Cleaner run successfully.');
    }
}
