<?php

namespace App\Listeners;

use App\Events\QRCodeSaved;
use App\Events\ShouldSaveQRCodeVariants;
use App\Support\QRCodeRedirectManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Log;

class OnQRCodeSaved
{
    use WriteLogs;

    private QRCodeSaved $event;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Handle the event.
     *
     * @param  \App\Events\QRCodeSaved  $event
     * @return void
     */
    public function handle(QRCodeSaved $event)
    {
        $this->event = $event;

        $this->logDebug('Generating redirect...');

        /** @var QRCodeRedirectManager */
        $qrcodeRedirectManager = app(QRCodeRedirectManager::class);

        $qrcodeRedirectManager->updateDestinationIfNeeded($event->qrcode);

        $this->logDebug('Redirect has been generated...');

        if ($this->shouldSaveVariants()) {

            $this->logDebug('Dispatching should save QR Code variants event ...');

            event(new ShouldSaveQRCodeVariants($event->qrcode));

            $this->logDebug('Event dispatched ....');
        }
    }

    private function shouldSaveVariants()
    {
        $qr = $this->event->qrcode;

        $wasRecentlyCreated = $qr->wasRecentlyCreated;

        $wasDataOrDesignChanged = $qr->wasChanged(['data', 'design']);

        $dataIsEmpty = empty(array_values((array)$qr->data));

        return $wasRecentlyCreated || $wasDataOrDesignChanged || $dataIsEmpty;
    }
}
