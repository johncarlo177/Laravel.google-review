<?php

namespace App\Support\Invoicing;

use App\Support\System\Traits\WriteLogs;

class TaxManager
{
    use WriteLogs;

    public const COLLECTION_TYPE_INCLUSIVE = 'inclusive';
    public const COLLECTION_TYPE_EXCLUSIVE = 'exclusive';

    public function __construct()
    {
        bcscale(4);
    }

    public function getRate()
    {
        return config('tax.rate');
    }

    public function isEnabled()
    {
        return config('tax.collection') === 'enabled';
    }

    public function getCollectionType()
    {
        $type = config('tax.collection_type') ?: static::COLLECTION_TYPE_INCLUSIVE;

        $this->logDebug($type);

        return $type;
    }


    public function isInclusive()
    {
        return $this->getCollectionType() === $this::COLLECTION_TYPE_INCLUSIVE;
    }

    public function isExclusive()
    {
        return $this->getCollectionType() === $this::COLLECTION_TYPE_EXCLUSIVE;
    }

    public function calculateTax($amount)
    {
        if ($this->getCollectionType() === static::COLLECTION_TYPE_INCLUSIVE) {
            return $this->calculateInclusiveTax($amount);
        }

        return $this->calculateExclusiveTax($amount);
    }

    public function calculateInclusiveTax($amount)
    {
        return $amount - $this->calculateNetAmount($amount);
    }

    public function calculateNetAmount($amount)
    {
        if ($this->getCollectionType() == static::COLLECTION_TYPE_EXCLUSIVE) {
            return $amount + $this->calculateExclusiveTax($amount);
        }


        $factor = 1 + (bcdiv($this->getRate(), 100));

        $net = bcdiv($amount, $factor);

        $this->logDebug('factor = %s, amount = %s, net = %s, fraction = %s', $factor, $amount, $net, bcdiv($this->getRate(), 100));

        return $net;
    }

    public function calculateExclusiveTax($amount)
    {
        return bcmul($amount, bcdiv($this->getRate(), 100));
    }
}
