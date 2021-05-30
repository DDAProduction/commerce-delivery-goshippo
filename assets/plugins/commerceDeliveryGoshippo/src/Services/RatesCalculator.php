<?php

namespace CommerceDeliveryGoshippo\Services;


use CommerceDeliveryGoshippo\Container;
use CommerceDeliveryGoshippo\Shipment;
use Shippo_Address;
use Shippo_Shipment;

class RatesCalculator
{
    /**
     * @var \DocumentParser
     */
    private $modx;

    public function __construct(Container $container)
    {
        $this->modx = $container->get(\DocumentParser::class);
    }

    public function calculator(Shipment $shipmentDto)
    {
        $requestParams = [
            'address_from'=> $shipmentDto->getSenderAddress()['fields'],
            'address_to' => $shipmentDto->getDestinationAddress()['fields'],
            'parcels' => $shipmentDto->getParcels(),
            'async' => false
        ];

        $this->modx->invokeEvent('OnCommerceDeliveryGoshippoBeforeRatesCalculate',[
            'request_params'=>&$requestParams,
        ]);

        $shipment = Shippo_Shipment::create([
            'address_from' => Shippo_Address::create($requestParams['address_from']),
            'address_to' => Shippo_Address::create($requestParams['address_to']),
            'parcels' => $requestParams['parcels'],
            'async' => $requestParams['async']
        ]);
        $ratesRequest = $shipment->__toArray(true);


        if ($ratesRequest["status"] !== "SUCCESS") {
            throw new \Exception(implode(',', $ratesRequest['messages']));
        }

        $rates = $ratesRequest['rates'];


        foreach ($rates as $key => $rate) {
            $rates[$key]['title'] = $rate['provider'] . ', ' . $rate['amount'] . ' ' . $rate['currency'] . ' (' . $rate['servicelevel']['name'] . ')';
        }

        $this->modx->invokeEvent('OnCommerceDeliveryGoshippoRatesCalculate',[
            'rates'=>&$rates,
        ]);


        return $rates;
    }


}