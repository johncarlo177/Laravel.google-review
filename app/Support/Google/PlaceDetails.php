<?php

namespace App\Support\Google;

class PlaceDetails
{
    public $url;
    public $name;
    public $rating;
    public $place_id;
    public $formatted_address;
    public $user_ratings_total;
    public $formatted_phone_number;
    public $international_phone_number;
    public $website;

    public static function withData($array)
    {
        $instance = new static;

        foreach (get_object_vars($instance) as $key => $_) {
            $instance->{$key} = @$array[$key];
        }

        return $instance;
    }
}
