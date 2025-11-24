<?php

namespace App\Listeners;

use App\Events\QRCodeSaved;

class GenerateQRCodeRedirect
{

    /**
     * Handle the event.
     *
     * @param  \App\Events\QRCodeSaved  $event
     * @return void
     */
    public function handle(QRCodeSaved $event)
    {
    }
}
