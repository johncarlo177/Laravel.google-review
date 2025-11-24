<?php

namespace App\Support\MaxMind;

use GeoIp2\Model\City;

class Location
{
    public string $iso_code;

    public string $city;

    public string $country;

    public float $accuracy_radius;

    public string $latitude;

    public string $longitude;

    public string $timezone;

    public function __construct(City $city)
    {
        $this->accuracy_radius = $city->location->accuracyRadius;

        $this->city = $city->city->name;

        $this->country = $city->country->name;

        $this->iso_code = $city->country->isoCode;

        $this->latitude = $city->location->latitude;

        $this->longitude = $city->location->longitude;

        $this->timezone = $city->location->timeZone;
    }
}
