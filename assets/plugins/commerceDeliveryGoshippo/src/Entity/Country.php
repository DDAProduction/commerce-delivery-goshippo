<?php
namespace CommerceDeliveryGoshippo\Entity;


class Country
{
    private $title;
    private $iso;
    /** @var  $requireState bool*/
    private $requireState;

    public function __construct($title, $iso, $requireState)
    {
        $this->title = $title;
        $this->iso = $iso;
        $this->requireState = $requireState;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getIso()
    {
        return $this->iso;
    }
    public function getRequireState()
    {
        return $this->requireState;
    }
}