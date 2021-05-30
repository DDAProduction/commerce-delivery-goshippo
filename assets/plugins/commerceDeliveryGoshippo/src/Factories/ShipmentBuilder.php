<?php


namespace CommerceDeliveryGoshippo\Factories;


use CommerceDeliveryGoshippo\Cache;
use CommerceDeliveryGoshippo\Container;
use CommerceDeliveryGoshippo\Services\Backend\BackendCartProductsGetter;
use CommerceDeliveryGoshippo\Services\Backend\BackendDestinationRequestAddressProvider;
use CommerceDeliveryGoshippo\Services\Front\FrontCartProductsGetter;
use CommerceDeliveryGoshippo\Services\Front\FrontDestinationRequestAddressProvider;
use CommerceDeliveryGoshippo\Shipment;

class ShipmentBuilder
{
    private static $shipment;

    public static function makeFromBackendRequest(){
        if (!is_null(self::$shipment)) {
            return self::$shipment;
        }

        $ci = Container::getInstance();

        $requestDestinationAddress = new BackendDestinationRequestAddressProvider($ci);
        $backendProductsGetter = new BackendCartProductsGetter();

        $senderAddress = (new \CommerceDeliveryGoshippo\Services\AddressSenderGetter($ci))->getAddress();
        $destinationAddress = (new \CommerceDeliveryGoshippo\Services\AddressDestinationGetter($ci))->getAddress($requestDestinationAddress->getAddress());
        $parcels = (new \CommerceDeliveryGoshippo\Services\ParcelsCalculator($ci))->calculate($backendProductsGetter->getProducts());


        $shipment = new Shipment($senderAddress, $destinationAddress, $parcels);

        self::$shipment = $shipment;
        return $shipment;
    }

    public static function makeFromFrontRequest()
    {
        if (!is_null(self::$shipment)) {
            return self::$shipment;
        }
        $ci = Container::getInstance();

        $requestDestinationAddress = new FrontDestinationRequestAddressProvider($ci);
        $FrontCartProductsGetter = new FrontCartProductsGetter();

        $cache = $ci->get(Cache::class);


        $senderAddress = (new \CommerceDeliveryGoshippo\Services\AddressSenderGetter($ci))->getAddress();
        $destinationAddress = (new \CommerceDeliveryGoshippo\Services\AddressDestinationGetter($ci))->getAddress($requestDestinationAddress->getAddress());
        $parcels = (new \CommerceDeliveryGoshippo\Services\ParcelsCalculator($ci))->calculate($FrontCartProductsGetter->getProducts());

        $shipment = new Shipment($senderAddress, $destinationAddress, $parcels);

        $ratesHash = $shipment->getHash();


        if ($cache->has($ratesHash) && !empty($_REQUEST['delivery_goshippo_rate_id'])) {
            $rates = $cache->get($ratesHash);

            foreach ($rates as $rate) {
                if ($rate['object_id'] == $_REQUEST['delivery_goshippo_rate_id']) {
                    $shipment->setRate($rate);
                }
            }
        }

        self::$shipment = $shipment;


        return $shipment;
    }

}