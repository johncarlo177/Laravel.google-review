<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QRCodeTemplatePolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function list(User $user)
    {
        return $user->permitted('qrcode-template.list');
    }

    public function manage(User $user)
    {
        return $user->permitted('qrcode-template.manage');
    }

    public function use(User $user)
    {
        return $user->permitted('qrcode-template.use');
    }
}
