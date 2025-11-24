<?php

namespace App\Policies\Restriction;

class FileRestrictor extends BaseRestrictor
{
    public function getRestrictedIds()
    {
        return [
            15029,
            15038,
            15033,
            15207,
            15206,
            15205,
        ];
    }
}
