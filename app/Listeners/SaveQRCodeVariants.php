<?php

namespace App\Listeners;

use App\Events\ShouldSaveQRCodeVariants;
use App\Interfaces\QRCodeGenerator;
use Illuminate\Support\Facades\Log;

class SaveQRCodeVariants
{
    private QRCodeGenerator $generator;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(QRCodeGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ShouldSaveQRCodeVariants  $event
     * @return void
     */
    public function handle(ShouldSaveQRCodeVariants $event)
    {
        if (!$event->qrcode) return;

        $this->generator->saveVariants($event->qrcode);
    }
}
