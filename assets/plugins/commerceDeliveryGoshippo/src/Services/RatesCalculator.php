<?php

namespace CommerceDeliveryGoshippo\Services;


use CommerceDeliveryGoshippo\Entity\Address;
use CommerceDeliveryGoshippo\Entity\Parcel;
use Shippo_Address;
use Shippo_Shipment;

class RatesCalculator
{
    public function calculator(Address $from, Address $to, Parcel $parcels)
    {

        $shipment = Shippo_Shipment::create([
            'address_from' => Shippo_Address::create($this->formedAddressRequest($from)),
            'address_to' => Shippo_Address::create($this->formedAddressRequest($to)),
            'parcels' => $this->formedParcels($parcels),
            'async' => false
        ]);
        $ratesRequest = $shipment->__toArray(true);

        if ($ratesRequest["status"] !== "SUCCESS") {
            throw new \Exception(implode(',', $ratesRequest['messages']));
        }
        $rates = $ratesRequest['rates'];

        foreach ($rates as $key => $rate) {
            $rates[$key]['title'] = $rate['provider'] . ', ' . $rate['amount'] . ' ' . $rate['currency'] . ' (' . $rate['servicelevel']['name'] . ')';
        }

        return $rates;
    }

    private function formedAddressRequest(Address $address)
    {
        return [
            'name' => $address->getName(),
            'street1' => $address->getStreet1(),
            'city' => $address->getCity(),
            'state' => $address->getState(),
            'zip' => $address->getZip(),
            'country' => $address->getCountry(),
        ];
    }

    private function formedParcels(Parcel $parcels)
    {
        return [
            'length' => $parcels->getLength(),
            'width' => $parcels->getWidth(),
            'height' => $parcels->getHeight(),
            'distance_unit' => $parcels->getDistanceUnit(),
            'weight' => $parcels->getWidth(),
            'mass_unit' => $parcels->getMassUnit(),
        ];
    }
}