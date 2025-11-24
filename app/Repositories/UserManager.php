<?php

namespace App\Repositories;

use App\Interfaces\UserManager as UserManagerInterface;
use App\Models\Folder;
use App\Models\QRCode;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Str;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Support\QRCodeManager;
use App\Support\DomainManager;
use App\Models\SubscriptionStatus;
use App\Notifications\Dynamic\InviteUser;
use App\Support\FolderManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;

class UserManager implements UserManagerInterface
{

    use WriteLogs;

    private FolderManager $folders;

    public function __construct()
    {
        $this->folders = new FolderManager();
    }

    public function getSuperAdmins()
    {
        return User::with('roles')->whereHas('roles', function ($role) {
            $role->where('super_admin', true);
        })->get();
    }

    public function getCurrentSubscription(User $user): ?Subscription
    {
        if ($user->is_sub) {
            return $this->getCurrentSubscription($user->parent_user);
        }

        if ($user->subscriptions->isEmpty()) return null;

        return $user
            ->subscriptions
            ->first(
                function (Subscription $subscription) use ($user) {

                    if ($subscription->statuses->isEmpty()) {

                        $this->logWarning(
                            'Cannot find statuses for subscription. subscription id = %s, user email = %s',
                            $subscription->id,
                            $user->email
                        );

                        return true;
                    }

                    return $subscription->statuses[0]->status
                        !== SubscriptionStatus::STATUS_PENDING_PAYMENT;
                }
            );
    }

    public function getClientUser(QRCode $qrcode)
    {
        $creator = $qrcode->user;

        if ($creator->parent_user) {
            return $creator->parent_user;
        }

        return $creator;
    }

    public function getCurrentPlan(User $user): ?SubscriptionPlan
    {
        return $this->getCurrentSubscription($user)?->subscription_plan;
    }

    public function deleteUser(User $user)
    {
        $user = User::with('transactions', 'subscriptions', 'qrcodes', 'qrcodes.redirect')->find($user->id);

        $user->transactions->each(function ($transaction) {
            $transaction->delete();
        });

        DB::delete('delete from user_roles where user_id = ?', [$user->id]);

        $user->subscriptions->each(function ($subscription) {
            $subscription->delete();
        });

        $qrcodeIds = $user->qrcodes?->pluck('id')->all();

        $qrcodeManager = new QRCodeManager();

        $qrcodeManager->deleteMany($qrcodeIds);

        $domainsManager = new DomainManager();

        $domainsManager->deleteDomainsOfUser($user);

        $user->delete();
    }

    public function changeRole(User $user, Role $role)
    {
        if (empty($user) || empty($role)) return;

        $relation_record = DB::table('user_roles')->where('user_id', $user->id)->first();

        $data = [
            'user_id' => $user->id,
            'role_id' => $role->id
        ];

        if (!$relation_record) {
            DB::table('user_roles')->insert($data);
        } else {
            DB::table('user_roles')->where('user_id', $user->id)->update($data);
        }
    }

    public function inviteUser(
        User $actor,
        string $name,
        string $email,
        $mobileNumber,
        array $folderIds
    ) {
        if (!config('droplet.is_large')) return abort(403);

        $user = new User();

        $user->name = $name;

        $user->email = $email;

        $generatedPassword = $this->randomPassword();

        $user->password = Hash::make($generatedPassword);

        $user->mobile_number = $mobileNumber;

        $user->parent_id = $actor->id;

        $user->is_sub = true;

        $user->save();

        $user->roles()->attach(Role::whereName('Sub User')->first());

        $user->markEmailAsVerified();

        $folders = collect($folderIds)
            ->map(fn($id) => Folder::findOrFail($id));

        $folders->each(
            fn($folder) => $this->folders->grantSubuserAccess(
                $user,
                $folder
            )
        );

        $user->notify(InviteUser::instance(
            accountOwner: $actor->name,
            generatedPassword: $generatedPassword,
            folderName: $folders->map(fn($f) => $f->name)->join(', '),
        ));

        return $user;
    }

    public function subUsers(User $actor)
    {
        $users = User::whereParentId($actor->id)->get();

        $users = $users->map(function ($user) {
            $user->subuser_folders = $this->folders->getSubuserFolders($user);

            return $user;
        });

        return $users;
    }

    public function deleteSubUser(User $subUser)
    {
        $qrcodes = new QRCodeManager();

        collect($subUser->qrcodes)
            ->each(function (QRCode $qrcode) use ($subUser, $qrcodes) {
                $qrcodes->changeUser(
                    $qrcode,
                    $this->getParentUser($subUser)->id
                );
            });

        $subUser->delete();

        return $subUser;
    }

    public function getParentUser(User $subUser)
    {
        if (!$subUser->parent_user) return $subUser;

        return $subUser->parent_user;
    }

    public function getUserIdsOnTheSameSubscription(User $user)
    {
        $parent = $user->parent_user;

        if (!$parent) {
            $parent = $user;
        }

        /** @var Collection */
        $ids = User::where('parent_id', $parent->id)
            ->select('id')
            ->get()
            ->pluck('id');

        $ids->add($parent->id);

        return $ids;
    }

    private function randomPassword()
    {
        if (app()->environment('local')) {
            return 'password';
        }

        return Str::random(8);
    }
}
