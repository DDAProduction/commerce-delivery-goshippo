<?php
namespace CommerceDeliveryGoshippo\Entity;


class Parcel
{

    public function getLength()
    {
        return $this->length;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function getDistanceUnit()
    {
        return $this->distanceUnit;
    }

    public function getMassUnit()
    {
        return $this->massUnit;
    }

    private $length;
    private $width;
    private $height;
    private $weight;
    private $distanceUnit;
    private $massUnit;

    public function __construct($length, $width, $height, $weight, $distanceUnit, $massUnit)
    {

        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
        $this->weight = $weight;
        $this->distanceUnit = $distanceUnit;
        $this->massUnit = $massUnit;
    }


}