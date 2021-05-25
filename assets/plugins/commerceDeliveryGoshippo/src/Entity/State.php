<?php
namespace CommerceDeliveryGoshippo\Entity;


class State
{

    public function getCountry()
    {
        return $this->country;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getIso()
    {
        return $this->iso;
    }
    /**
     * @var $country Country|null
     */
    private $country;
    private $title;
    private $iso;

    public function __construct($country, $title, $iso)
    {
        $this->country = $country;
        $this->title = $title;
        $this->iso = $iso;
    }
}