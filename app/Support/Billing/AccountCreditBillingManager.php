<?php

namespace App\Support\Billing;

use App\Models\Config;
use App\Models\QRCode;
use App\Models\User;
use App\Support\QRCodeTypes\QRCodeTypeManager;
use App\Support\System\Traits\WriteLogs;
use LogicException;

class AccountCreditBillingManager
{
    use WriteLogs;

    private User $user;

    private QRCodeTypeManager $qrcodeTypeManager;

    public function __construct()
    {
        $this->qrcodeTypeManager = new QRCodeTypeManager();
    }

    public function forUser($user)
    {
        if (is_numeric($user)) {
            $user = User::find($user);
        }

        $this->user = $user;

        return $this;
    }

    public function dynamicQRCodePrice()
    {
        return Config::get('account_credit.dynamic_qrcode_price');
    }

    public function staticQRCodePrice()
    {
        return Config::get('account_credit.static_qrcode_price') ?? 0;
    }

    public function deductQRCodePrice(QRCode $qrcode)
    {
        if ($this->qrcodeTypeManager->isDynamic($qrcode->type)) {
            $this->deductAccountBalance($this->dynamicQRCodePrice());
        } else {
            $this->deductAccountBalance($this->staticQRCodePrice());
        }
    }

    public function getAccountBalance()
    {
        return $this->user->getMeta($this->metaKey());
    }

    public function addAccountBalance($amount)
    {
        return $this->setAccountBalance(
            $this->getAccountBalance() + $amount
        );
    }

    private function deductAccountBalance($amount)
    {
        $newBalance = $this->getAccountBalance() - $amount;

        if ($newBalance < 0) {
            throw new LogicException(
                sprintf('Balance cannot go below zero, trying to deduct %s from %s', $amount, $this->getAccountBalance())
            );
        }

        $this->logDebugf('Deducting %s from account balance. New balance is %s', $amount, $newBalance);

        return $this->setAccountBalance(
            $newBalance
        );
    }

    public function setAccountBalance($amount)
    {
        return $this->user->setMeta($this->metaKey(), $amount);
    }

    private function metaKey()
    {
        return 'account_credit';
    }
}
