<?php

namespace App\Repositories;

use App\Interfaces\DeviceInfo as Contract;
use App\Support\System\Traits\WriteLogs;
use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Client\Browser;
use DeviceDetector\Parser\Device\AbstractDeviceParser;

class DeviceInfo implements Contract
{
    use WriteLogs;

    private $info = null;

    private $userAgent = null;

    public function __construct()
    {
        $this->userAgent = @$_SERVER['HTTP_USER_AGENT'] ?? '';

        if (app()->runningInConsole()) return;

        if (empty($this->userAgent)) {
            abort(422, t('Device is not recognized.'));
        }

        AbstractDeviceParser::setVersionTruncation(
            AbstractDeviceParser::VERSION_TRUNCATION_NONE
        );

        $clientHints = ClientHints::factory($_SERVER); // client hints are optional

        $this->info = new DeviceDetector($this->userAgent, $clientHints);

        $this->info?->parse();
    }

    public function getUserAgent()
    {
        return $this?->userAgent;
    }

    public function isBot()
    {
        return $this->info?->isBot();
    }

    public function is_iPhone()
    {
        return preg_match('/ios/i', $this->getOSName());
    }

    public function isAndroid()
    {
        return preg_match('/android/i', $this->getOSName());
    }

    public function getDeviceName()
    {
        return $this->info?->getDeviceName();
    }

    public function getDeviceBrand()
    {
        return $this->info?->getBrandName();
    }

    public function getDeviceModel()
    {
        return $this->info?->getModel();
    }

    public function getOSName()
    {
        return @$this->info?->getOs()['name'];
    }

    public function getOSVersion()
    {
        return @$this->info?->getOs()['version'];
    }

    public function getClientType()
    {
        return @$this->info?->getClient()['type'];
    }

    public function getClientName()
    {
        return @$this->info?->getClient()['name'];
    }

    public function getClientVersion()
    {
        return @$this->info?->getClient()['version'];
    }

    public function getBrowser()
    {
        if (!$this->info) {
            return;
        }

        $browserFamily = Browser::getBrowserFamily($this->info?->getClient('name'));

        $this->logDebug('browser = %s, client = %s', $browserFamily, $this->info?->getClient('name'));

        return $browserFamily;
    }
}
