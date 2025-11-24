<?php

namespace App\Policies;

use App\Models\QRCodeRedirect;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QRCodeRedirectPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function update(User $user, QRCodeRedirect $redirect)
    {
        if ($user->permitted('qrcode-redirect.updateSlug-any')) return true;

        return
            $user->permitted('qrcode-redirect.updateSlug') &&
            $redirect->qrcode->user_id == $user->id;
    }
}
