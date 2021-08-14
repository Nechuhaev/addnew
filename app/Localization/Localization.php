<?php

namespace App\Localization;

use App\AdCountry;

class Localization
{
    public static $instance = null;

    protected $country;

    private function __construct() {}

    public static function getInstance()
    {
        return new Localization();
    }


    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry(AdCountry $country)
    {
         $this->country = $country;
    }

    public function ads() {
        return $this->country->ads();
    }

    public function cities() {
        return $this->country->cities();
    }

    public function citiesIds()
    {
        return $this->cities()->get()->map(function ($city) {
            return $city->id;
        })->toArray();
    }

}