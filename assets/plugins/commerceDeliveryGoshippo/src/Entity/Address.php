<?php
namespace CommerceDeliveryGoshippo\Entity;


class Address
{
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Country|null
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getStreet1()
    {
        return $this->street1;
    }

    /**
     * @return State|null
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }
    /** @var $name string */
    private $name;
    /** @var $country Country|null */
    private $country;
    /** @var $city string */
    private $city;
    /** @var $street1 string */
    private $street1;
    /** @var $state State|null */
    private $state;
    /** @var $zip string */
    private $zip;

    public function __construct($name, $country, $city, $street1, $state, $zip)
    {

        $this->name = $name;
        $this->country = $country;
        $this->city = $city;
        $this->street1 = $street1;
        $this->state = $state;
        $this->zip = $zip;
    }

    public function isFullAddress(){
        return
            !empty($this->name) &&
            !is_null($this->country) &&
            ($this->country->getRequireState() === false || !is_null($this->state)) &&
            !empty($this->zip) &&
            !empty($this->city) &&
            !empty($this->street1)
            ;
    }




}