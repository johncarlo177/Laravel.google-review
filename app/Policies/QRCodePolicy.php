<?php

namespace App\Policies;

use App\Interfaces\SubscriptionManager;
use App\Models\QRCode;
use App\Models\User;
use App\Policies\Restriction\QRCodeRestrictor;
use App\Support\FolderManager;
use App\Support\QRCodeTypes\QRCodeTypeManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Auth\Access\HandlesAuthorization;

class QRCodePolicy extends BasePolicy
{
    use WriteLogs;

    use HandlesAuthorization;

    private FolderManager $folders;
    private SubscriptionManager $subscriptions;

    public function __construct()
    {
        $this->folders = new FolderManager;
        $this->subscriptions = app(SubscriptionManager::class);
    }

    private function createdBySubUser(QRCode $qrcode, User $parent)
    {
        $ids = $parent->sub_users()->pluck('id');

        if ($ids->isEmpty()) return false;

        return !empty($ids->first(fn($id) => $id == $qrcode->user_id));
    }

    private function createdByUser(QRCode $qrcode, User $user)
    {
        return $qrcode->user_id == $user->id;
    }

    private function subUserHasAccessToQRCodeFolder(QRCode $qrcode, User $user)
    {
        $ids = $this->folders->getSubuserFolders($user)->pluck('id');

        return !empty($ids->first(fn($id) => $id == $qrcode->folder_id));
    }

    private function hasAccessTo(QRCode $qrcode, User $user)
    {
        if ($user->is_sub) {
            return $this->subUserHasAccessToQRCodeFolder($qrcode, $user);
        }

        return $this->createdByUser($qrcode, $user) || $this->createdBySubUser($qrcode, $user);
    }

    public function changeUser(User $user)
    {
        return $user->permitted('user.change-user');
    }

    public function setPincode(User $user, QRCode $qrcode)
    {
        if ($this->canListAll($user)) return true;

        return $this->hasAccessTo($qrcode, $user);
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function list(User $user)
    {
        return $user->permitted('qrcode.list');
    }

    public function listAll(User $user)
    {
        return $user->permitted('qrcode.list-all');
    }

    public function show(User $user, QRCode $qrcode)
    {
        if ($user->permitted('qrcode.show-any')) {
            return true;
        }

        $permitted =  $user->permitted('qrcode.show') && $this->hasAccessTo($qrcode, $user);

        return $permitted;
    }

    public function store(User $user)
    {
        if ($this->canListAll($user)) return true;

        if (!(new QRCodeTypeManager())->isDynamic(request()->input('type'))) {
            return $user->permitted('qrcode.store');
        }

        if (
            $this->subscriptions->userDynamicQRCodesLimitReached($user, request()->input('type'))
        ) {
            return false;
        }

        return $user->permitted('qrcode.store');
    }

    public function update(User $user, QRCode $qrcode)
    {
        $this->logDebug('Within update policy');

        QRCodeRestrictor::make($qrcode->id)
            ->applyRestrictions();

        if ($user->permitted('qrcode.update-any')) {
            return true;
        }

        return $user->permitted('qrcode.update') && $this->hasAccessTo($qrcode, $user);
    }

    public function reset(User $user, QRCode $qrcode)
    {
        $this->logDebug('Within update policy');

        QRCodeRestrictor::make($qrcode->id)
            ->applyRestrictions();

        if ($user->permitted('qrcode.update-any')) {
            return true;
        }

        return $user->permitted('qrcode.update') && $this->hasAccessTo($qrcode, $user);
    }

    public function archive(User $user, QRCode $qrcode)
    {
        if ($user->permitted('qrcode.archive-any')) {
            return true;
        }

        return $user->permitted('qrcode.archive') && $this->hasAccessTo($qrcode, $user);
    }



    public function showStats(User $user, QRCode $qrcode)
    {
        if ($user->permitted('qrcode.showStats-any')) {
            return true;
        }

        $permitted = $user->permitted('qrcode.showStats');

        if (!$permitted) return false;

        return $this->hasAccessTo($qrcode, $user);
    }

    public function destroy(User $user, QRCode $qrcode)
    {
        $this->restrictDemo();

        if ($user->permitted('qrcode.destroy-any')) {
            return true;
        }

        return $user->permitted('qrcode.destroy') &&
            ($this->createdByUser($qrcode, $user)
                ||
                $this->createdBySubUser($qrcode, $user)
            );
    }

    public static function canListAll(User $user)
    {
        return $user->permitted('qrcode.list-all');
    }
}
