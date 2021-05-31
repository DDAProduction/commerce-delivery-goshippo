<?php

namespace CommerceDeliveryGoshippo\Services;


use CommerceDeliveryGoshippo\Container;
use CommerceDeliveryGoshippo\Shipment;
use Helpers\Lexicon;
use Shippo_Address;
use Shippo_Shipment;

class RatesCalculator
{
    /**
     * @var \DocumentParser
     */
    private $modx;
    /**
     * @var false|Lexicon|mixed
     */
    private $lexicon;

    public function __construct(Container $container)
    {
        $this->modx = $container->get(\DocumentParser::class);
        $this->lexicon = $container->get(Lexicon::class);
    }

    public function calculator(Shipment $shipmentDto)
    {

        $addressFrom = $shipmentDto->getSenderAddress()['fields'];
        $addressTo = $shipmentDto->getDestinationAddress()['fields'];
        $parcels = $shipmentDto->getParcels();



        $this->modx->invokeEvent('OnCommerceDeliveryGoshippoBeforeRatesCalculate',[
            'address_from'=>&$addressFrom,
            'address_to'=>&$addressTo,
            'parcels'=>&$parcels,
        ]);


        $shipment = Shippo_Shipment::create([
            'address_from' => Shippo_Address::create($addressFrom),
            'address_to' => Shippo_Address::create($addressTo),
            'parcels' => $parcels,
            'async' => false
        ]);
        $ratesRequest = $shipment->__toArray(true);


        if ($ratesRequest["status"] !== "SUCCESS") {

            throw new \Exception(implode(',', $ratesRequest['messages']));
        }

        $rates = $ratesRequest['rates'];
        if(empty($rates)){
            throw new \Exception($this->lexicon->get('rates_get_failed'));
        }


        foreach ($rates as $key => $rate) {
            $rates[$key]['title'] = $rate['provider'] . ', ' . $rate['amount'] . ' ' . $rate['currency'] . ' (' . $rate['servicelevel']['name'] . ')';
        }



        $this->modx->invokeEvent('OnCommerceDeliveryGoshippoRatesCalculate',[
            'address_from'=>$addressFrom,
            'address_to'=>$addressTo,
            'parcels'=>$parcels,
            'rates'=>&$rates,
        ]);


        return $rates;
    }


}