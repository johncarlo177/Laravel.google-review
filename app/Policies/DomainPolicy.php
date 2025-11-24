<?php

namespace App\Policies;

use App\Http\Middleware\ErrorMessageMiddleware;
use App\Interfaces\SubscriptionManager;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class DomainPolicy extends BasePolicy
{
    private SubscriptionManager $subscriptions;

    use HandlesAuthorization;

    public function __construct()
    {
        $this->subscriptions = app(SubscriptionManager::class);
    }

    public function list(User $user)
    {
        return $user->permitted('domain.list-all');
    }

    /**
     * Determine whether the user can show the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function show(User $user, Domain $domain)
    {
        if ($user->permitted('domain.show-any')) return true;

        if (!$user->permitted('domain.show')) return false;

        if ($domain->availability === Domain::AVAILABILITY_PUBLIC) return true;

        return $user->id == $domain->user_id;
    }

    /**
     * Determine whether the user can store models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function store(User $user)
    {
        if ($user->permitted('domain.store')) return true;

        if (!$user->permitted('domain.add')) return false;

        if ($this->subscriptions->userDomainsLimitReached($user)) {
            $this->fail(t('Domain limits reached.'));
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Domain $domain)
    {
        $this->restrictReadOnly($domain, 'Cannot update read only domain.');

        return $user->permitted('domain.update-any');
    }

    public function updateStatus(User $user, Domain $domain, string $status)
    {
        $this->restrictReadOnly($domain, 'Cannot change status of read only domain.');

        if (!collect(Domain::getStatuses())->first(fn ($s) => $s == $status)) {
            ErrorMessageMiddleware::setMessage(t('Status is not supported'));
            abort(422);
        }

        if ($user->permitted('domain.updateStatus-any')) return true;

        return $user->permitted(
            'domain.updateStatus'
        ) && $domain->user_id == $user->id
            && $domain->status === Domain::STATUS_DRAFT
            && $status == Domain::STATUS_IN_PROGRESS;
    }

    public function updateAvailability(User $user, Domain $domain)
    {
        $this->restrictReadOnly($domain, 'Cannot change availability of read only domain.');

        return $user->permitted('domain.update-any');
    }

    public function setDefault(User $user, Domain $domain)
    {
        $this->restrictDemo();

        if (!$domain->user->isSuperAdmin()) {
            return $this->fail(t('User submitted domain cannot be default.'));
        }

        return $user->permitted('domain.setDefault');
    }

    private function restrictReadOnly(Domain $domain, $failMessage)
    {
        if (!$domain->readonly) {
            return;
        }

        ErrorMessageMiddleware::setMessage(t($failMessage));

        abort(403);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function destroy(User $user, Domain $domain)
    {
        $this->restrictReadOnly($domain, 'Read only domain cannot be deleted');

        if ($user->permitted('domain.destroy-any')) return true;

        return $user->permitted('domain.destroy') && $domain->user_id == $user->id;
    }
}
