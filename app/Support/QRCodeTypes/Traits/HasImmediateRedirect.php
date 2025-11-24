<?php

namespace App\Support\QRCodeTypes\Traits;

use App\Models\QRCode;

trait HasImmediateRedirect
{
    protected function isSafeBrowsingEnabled()
    {
        $value = config('qrcode.safe_browsing_redirect');

        return $value === 'enabled';
    }

    protected function shouldShowSafeBrowsingRedirect(QRCode $qrcode)
    {
        return !$this->isRedirectingToSameDomain($qrcode) && $this->isSafeBrowsingEnabled();
    }

    protected function isRedirectingToSameDomain(QRCode $qrcode)
    {
        $destination = $qrcode->redirect->destination;

        $appHost = parse_url(url('/'), PHP_URL_HOST);

        $destHost = parse_url($destination, PHP_URL_HOST);

        return $destHost === $appHost;
    }

    public function renderImmediateRedirect(QRCode $qrcode)
    {
        if ($this->shouldShowSafeBrowsingRedirect($qrcode)) {
            return view('qrcode.pages.safe-browsing-redirect', [
                'destination' => $qrcode->redirect->destination
            ]);
        }

        return redirect($qrcode->redirect->destination)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
