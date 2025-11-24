<?php

namespace App\Interfaces;

use App\Models\Currency;

interface CurrencyManager
{
    public function store(array $data);

    public function update(Currency $currency, array $data);

    public function delete(Currency $currency);

    public function enable(Currency $currency): Currency;

    public function enabledCurrency(): Currency;

    /**
     * Get the currently enabled currency.
     * @return Currency 
     */
    public static function currency(): Currency;

    public function format($price): string;

    public function formatNumber($number): string;
}
