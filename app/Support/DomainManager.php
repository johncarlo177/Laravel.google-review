<?php

namespace App\Support;

use Exception;
use App\Models\User;
use App\Models\Domain;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\UserManager;
use App\Notifications\DomainPublished;
use App\Notifications\DomainSubmitted;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class DomainManager
{
    const DOMAIN_CONNECTION_ROUTE = 'domain-connection';

    public function deleteDomainsOfUser(User $user)
    {
        return Domain::where('user_id', $user->id)->delete();
    }

    public function orderQuery(Builder $query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    public function getUsableDomains(User $usableBy)
    {
        $usableBy = (app(UserManager::class))->getParentUser($usableBy);

        $query = Domain::query();

        $query
            ->where(function ($query) use ($usableBy) {
                $query
                    ->where('user_id', $usableBy->id)
                    ->orWhere('availability', 'public');
            })->where('status', Domain::STATUS_PUBLISHED);

        $this->orderQuery($query);

        return $query->get();
    }

    public function store($data, User $user)
    {
        $domain = new Domain();

        $domain->user_id = $user->id;

        return $this->save($data, $domain, $user);
    }

    public function update($data, Domain $domain, User $user)
    {
        return $this->save($data, $domain, $user);
    }

    private function save($data, Domain $domain, User $user)
    {
        $domain->fill($data);

        if ($user->permitted('domain.update-any')) {
            $domain->sort_order = @$data['sort_order'] ?? 100;
        }

        $domain->save();

        return $domain;
    }

    public function updateStatus(Domain $domain, $status)
    {
        if ($status === Domain::STATUS_PUBLISHED) {
            if (!$this->applicationIsAccessible($domain)) {
                $this->throwValidationError('status', t('Application is not accessible.'));
            }
        }

        $domain->status = $status;

        $domain->save();

        if ($status === Domain::STATUS_IN_PROGRESS) {
            $this->notifyAdmins(new DomainSubmitted($domain));
        }

        if ($status === Domain::STATUS_PUBLISHED) {
            $domain->user->notify(new DomainPublished($domain));
        }

        return $domain;
    }

    public function updateAvailability(Domain $domain, $availability)
    {
        if ($availability == Domain::AVAILABILITY_PUBLIC) {
            if (!$domain->user->isSuperAdmin()) {
                $this->throwValidationError('availability', t('User domains cannot made public.'));
            }
        }

        $domain->availability = $availability;

        $domain->save();

        return $domain;
    }

    public function setDefault(Domain $domain)
    {
        DB::table('domains')->update([
            'is_default' => false
        ]);

        $domain->is_default = true;

        $domain->save();

        return $domain;
    }

    public function applicationIsAccessible(Domain $domain)
    {
        try {
            $result = Http::get(
                sprintf(
                    '%s://%s/domain-connection',
                    $domain->protocol,
                    $domain->host
                )
            )->body();

            return Crypt::decryptString($result) === $this->connectionStringSeed();
        } catch (Exception $ex) {
            return false;
        }
    }

    public function connectionString()
    {
        $value = Crypt::encryptString($this->connectionStringSeed());

        return $value;
    }

    private function connectionStringSeed()
    {
        return $this::class . '::connectionStringSeed';
    }

    public function dnsIsConfigured($host)
    {
        $target = @dns_get_record($host, DNS_CNAME)[0]['target'];

        return [
            'success' => parse_url(url('/'))['host'] === $target,
            'currentValue' => $target
        ];
    }

    public function getDefaultDomain()
    {
        $domain = Domain::where('is_default', true)->first();

        return $domain;
    }

    public function domainUrl(Domain $domain, $path)
    {
        $url = sprintf('%s/%s', $domain->host, $path);

        $url = preg_replace('/\/+/', '/', $url);

        $url = sprintf('%s://%s', $domain->protocol, $url);

        return $url;
    }

    public function getPublishedDomainsOfUser(User $user)
    {
        return $user->domains->filter(function ($domain) {
            return $domain->status === Domain::STATUS_PUBLISHED;
        });
    }

    private function throwValidationError($field, $message)
    {
        $validator = Validator::make([], []);

        $validator->after(function ($validator) use ($field, $message) {
            $validator->errors()->add($field, $message);
        });

        $validator->validate();
    }

    private function notifyAdmins($notification)
    {
        /** @var UserManager */
        $users = app(UserManager::class);

        $users->getSuperAdmins()->each(
            fn(User $user) =>
            $user->notify($notification)
        );
    }
}
