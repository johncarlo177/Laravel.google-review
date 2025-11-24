<?php

namespace App\Policies;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CurrencyPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function list(User $user)
    {
        return $user->permitted('currency.list-all');
    }

    /**
     * Determine whether the user can show the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function show(User $user, Currency $currency)
    {
        return $user->permitted('currency.show-any');
    }

    /**
     * Determine whether the user can store models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function store(User $user)
    {
        return $user->permitted('currency.store');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Currency $currency)
    {
        return $user->permitted('currency.update-any');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Currency  $currency
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function destroy(User $user, Currency $currency)
    {
        $this->restrictDemo();

        return $user->permitted('currency.destroy-any');
    }

    public function enable(User $user, Currency $currency)
    {
        if (!env('ALLOW_ADDING_NEW_PAYMENT_PROCESSOR_IN_DEMO')) {
            $this->restrictDemo();
        }

        return $user->permitted('currency.enable');
    }
}
