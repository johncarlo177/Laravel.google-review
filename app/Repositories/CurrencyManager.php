<?php

namespace App\Repositories;

use App\Interfaces\CurrencyManager as CurrencyManagerInterface;
use App\Models\Currency;

use function PHPSTORM_META\map;

class CurrencyManager implements CurrencyManagerInterface
{
    private static ?Currency $__enabledCurrency = null;

    public function store(array $data)
    {
        $currency = new Currency;

        $currency->fill($data);

        $currency->save();

        return $currency;
    }

    public function update(Currency $currency, array $data)
    {
        $currency->fill($data);

        $currency->save();

        return $currency;
    }

    public function delete(Currency $currency)
    {
        $currency->delete();

        return $currency;
    }

    public function enable(Currency $currency): Currency
    {
        $this->disableAllCurrencies();

        $currency->is_enabled = true;

        $currency->save();

        return $currency;
    }

    private function disableAllCurrencies()
    {
        Currency::all()->each(function ($c) {
            $c->is_enabled = false;
            $c->save();
        });
    }

    public function enabledCurrency(): Currency
    {
        if (!$this::$__enabledCurrency) {
            // 
            $enabledCurrency = Currency::where('is_enabled', true)->first();

            if (!$enabledCurrency) {
                $enabledCurrency = $this->defaultCurrency();
            }

            $this::$__enabledCurrency = $enabledCurrency;
        }

        return $this::$__enabledCurrency;
    }

    public static function currency(): Currency
    {
        return (new static)->enabledCurrency();
    }

    public function defaultCurrency()
    {
        $currency = new Currency();

        $currency->name = 'United States Dollar';

        $currency->currency_code = 'USD';

        $currency->symbol = '$';

        $currency->symbol_position = 'before';

        $currency->thousands_separator = ',';

        $currency->decimal_separator = '.';

        $currency->decimal_separator_enabled = 'enabled';

        return $currency;
    }

    public function format($price): string
    {
        if (empty($price)) {
            $price = '0';
        }

        $currency = $this->enabledCurrency();

        if ($this->isCurrencyBefore($currency)) {
            return sprintf('%s%s', $currency->symbol, $this->formatNumber($price));
        }

        return sprintf('%s%s', $this->formatNumber($price), $currency->symbol);
    }

    public function formatNumber($number): string
    {
        $decimals = 0;

        if ($this->currency()->decimal_separator_enabled === 'enabled') {
            $decimals = 2;
        }

        return number_format(
            $number,
            $decimals,
            $this->currency()->decimal_separator,
            $this->currency()->thousands_separator
        );
    }

    private function isCurrencyBefore(Currency $currency)
    {
        $position = $currency->symbol_position;

        return empty($position) || $position == 'before';
    }
}
