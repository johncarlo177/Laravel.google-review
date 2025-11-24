<?php

namespace App\Support\QRCodeTypes\Ads;


use App\Interfaces\UserManager;
use App\Models\QRCode;
use App\Models\SubscriptionPlan;

use App\Support\System\Traits\WriteLogs;

class AdsManager
{
    use WriteLogs;

    private UserManager $users;

    private QRCode $qrcode;

    public function __construct()
    {
        $this->users = app(UserManager::class);
    }

    private function ticket()
    {
        return Ticket::withRequest(request())
            ->withTimeout($this->timeout());
    }

    public function withQRCode(QRCode $qrcode)
    {
        $this->qrcode = $qrcode;

        return $this;
    }

    private function plan(): ?SubscriptionPlan
    {
        return $this->users->getCurrentPlan($this->qrcode->user);
    }

    public function shouldShowAds()
    {
        if ($this->ticket()->allowsEntry()) {
            return false;
        }

        return $this->planAdsEnabled();
    }

    protected function planAdsEnabled()
    {
        return $this->plan()?->show_ads === 'enabled';
    }

    public function code()
    {
        return $this->plan()?->ads_code;
    }

    public function timeout()
    {
        return $this->plan()?->ads_timeout;
    }

    public function render()
    {
        if ($this->ticket()->allowsEntry()) {
            return redirect()->to(request()->fullUrl());
        }

        if ($this->ticket()->shouldIssue()) {
            return $this->ticket()->issue();
        }

        return view('qrcode.pages.ads', ['ads' => $this]);
    }
}
