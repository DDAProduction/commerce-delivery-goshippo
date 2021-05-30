<?php
namespace CommerceDeliveryGoshippo;


use CommerceDeliveryGoshippo\Services\Front\FrontCartProductsGetter;
use CommerceDeliveryGoshippo\Services\Front\FrontDestinationRequestAddressProvider;

class Shipment
{



    private $senderAddress;

    private $rate;

    public function getSenderAddress()
    {
        return $this->senderAddress;
    }

    public function getDestinationAddress()
    {
        return $this->destinationAddress;
    }

    public function getParcels()
    {
        return $this->parcels;
    }

    private $destinationAddress;
    private $parcels;

    public function __construct($senderAddress, $destinationAddress, $parcels)
    {
        $this->senderAddress = $senderAddress;
        $this->destinationAddress = $destinationAddress;
        $this->parcels = $parcels;
    }

    public function getHash(){
        return md5(json_encode(['destination' => $this->getDestinationAddress()['fields'], 'parcels' => $this->getParcels()]));
    }


    public function getRate()
    {
        return $this->rate;
    }

    public function setRate($rate)
    {
        $this->rate = $rate;
    }
}