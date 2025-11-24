<?php

namespace App\Listeners;

use App\Events\QRCodeLogoUploaded;
use App\Events\ShouldSaveQRCodeVariants;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * @deprecated
 */
class OnQRCodeLogoUploaded
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(QRCodeLogoUploaded $event)
    {
    }
}
