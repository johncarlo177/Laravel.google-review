<?php

namespace App\Policies\Restriction;



class QRCodeRestrictor extends BaseRestrictor
{
    public function getRestrictedIds()
    {
        return [
            130001,
            130002,
            130196,
        ];
    }
}
