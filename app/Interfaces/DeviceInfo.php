<?php

namespace App\Interfaces;

interface DeviceInfo
{
    public function isBot();
    public function getDeviceName();
    public function getDeviceBrand();
    public function getDeviceModel();
    public function getOSName();
    public function getOSVersion();
    public function getClientType();
    public function getClientName();
    public function getClientVersion();

    public function isAndroid();

    public function is_iPhone();
}
