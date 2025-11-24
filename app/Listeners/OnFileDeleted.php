<?php

namespace App\Listeners;

use App\Events\FileDeleted;

use App\Events\ShouldSaveQRCodeVariants;

use App\Models\QRCode;


class OnFileDeleted
{
    private $event;

    private $file;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\FileDeleted  $event
     * @return void
     */
    public function handle(FileDeleted $event)
    {
        $this->event = $event;
        $this->file = $event->file;

        if (
            $this->file->attachable_type === QRCode::class
        ) {
            event(new ShouldSaveQRCodeVariants(QRCode::find($this->file->attachable_id)));
        }
    }
}
