<?php

namespace App\Notifications\Dynamic\Interfaces;

use App\Models\User;

interface ShouldBroadcast
{
    public function shouldBroadcast(User $user): bool;
}
