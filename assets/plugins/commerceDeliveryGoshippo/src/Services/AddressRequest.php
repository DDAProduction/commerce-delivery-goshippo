<?php
namespace CommerceDeliveryGoshippo\Services;


use CommerceDeliveryGoshippo\Cache;
use CommerceDeliveryGoshippo\Repositories\CountryRepository;
use CommerceDeliveryGoshippo\Repositories\StateRepository;
use Helpers\Config;
use Shippo_Address;
use Shippo_Shipment;

class AddressRequest
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @return null
     */
    public function getSelectedState()
    {
        return $this->selectedState;
    }

    /**
     * @return null
     */
    public function getSelectedCountry()
    {
        return $this->selectedCountry;
    }

    /**
     * @var Config
     */
    private $config;
    /**
     * @var array
     */
    private $request;



    private $selectedState = null;
    private $selectedCountry = null;
    /**
     * @var CountryRepository
     */
    private $countryRepository;
    /**
     * @var StateRepository
     */
    private $stateRepository;



    public function __construct(Cache $cache,Config $config, CountryRepository $countryRepository, StateRepository $stateRepository,array $request)
    {
        $this->config = $config;
        $this->request = $request;

        $this->countryRepository = $countryRepository;
        $this->stateRepository = $stateRepository;

        $this->initCountries();
        $this->initStates();

        $this->cache = $cache;
    }

    public function getDataForShipmentRequest(){



        $addressTo = array_filter([
            'name' => $this->getFullNameFromRequest(),
            'street1' => $this->request['delivery_goshippo_street'],
            'city' => $this->request['delivery_goshippo_city'],
            'state' => $this->request['delivery_goshippo_state'],
            'zip' => $this->request['delivery_goshippo_zip'],
            'country' => $this->request['delivery_goshippo_country'],
        ]);

        $addressFrom = array_filter([
            'name' => $this->config->getCFGDef('from_name'),
            'street1' => $this->config->getCFGDef('from_street1'),
            'city' => $this->config->getCFGDef('from_city'),
            'state' => $this->config->getCFGDef('from_state'),
            'zip' => $this->config->getCFGDef('from_zip'),
            'country' => $this->config->getCFGDef('from_country'),
        ]);



        $cart = ci()->carts->getCart('products');
        $items = $cart->getItems();


        $sideSize = (new \CommerceDeliveryGoshippo\Services\PackageSizeCalculator($this->config))->calculate($items);
        $weight = (new \CommerceDeliveryGoshippo\Services\PackageWeightCalculator($this->config))->calculate($items);

        $parcel = array(
            'length' => $sideSize,
            'width' => $sideSize,
            'height' => $sideSize,
            'distance_unit' => $this->config->getCFGDef('distance_units'),
            'weight' => $weight,
            'mass_unit' => $this->config->getCFGDef('mass_units'),
        );

        return [
            'address_from' => $addressFrom,
            'address_to' => $addressTo,
            'parcels' => [$parcel],

        ];


    }

    public function getRates()
    {
        $start = microtime(true);


        $shipmentRequestData = $this->getDataForShipmentRequest();


        if (!$this->isFullAddress()) {
            throw new \Exception('Address is not full');
        }

        $ratesRequestHash = $this->getRateRequestHash();




        if ($this->cache->has($ratesRequestHash)) {
            $rates = $this->cache->get($ratesRequestHash);
        } else {

            $shipment = Shippo_Shipment::create([
                'address_from' => Shippo_Address::create($shipmentRequestData['address_from']),
                'address_to' => Shippo_Address::create($shipmentRequestData['address_to']),
                'parcels' => $shipmentRequestData['parcels'],
                'async' => false
            ]);
            $ratesRequest = $shipment->__toArray(true);

            if ($ratesRequest["status"] == "SUCCESS") {
                $rates = $ratesRequest['rates'];
                $this->cache->set($ratesRequestHash, $rates);
            } else {
                throw new \Exception(implode(',',$ratesRequest['messages']));
            }
        }



        return $rates;
    }

    public function isFullAddress(){

        $fullName = $this->getFullNameFromRequest();

        return
            !empty($fullName) &&
            !empty($this->selectedCountry) &&
            ($this->selectedCountry['require_state'] == 0 || !empty($this->selectedState)) &&
            !empty($this->request['delivery_goshippo_zip']) &&
            !empty($this->request['delivery_goshippo_city']) &&
            !empty($this->request['delivery_goshippo_street'])
        ;
    }

    public function getFullNameFromRequest(){
        return $this->request[$this->config->getCFGDef('full_name_field')];
    }

    public function initStates(){

        if (!empty($this->selectedCountry) && !empty($_REQUEST['delivery_goshippo_state'])) {
            $this->selectedState = $this->stateRepository->getState($_REQUEST['delivery_goshippo_state'],$this->selectedCountry['iso']);
        }
    }
    private function initCountries()
    {
        $this->selectedCountry = $this->countryRepository->getSelectedCountryFromRequest($this->request);

    }

    public function getRateRequestHash()
    {
        $shipmentRequestData = $this->getDataForShipmentRequest();

        return md5(json_encode(['addressToRequest' => $shipmentRequestData['address_to'], 'parcel' => $shipmentRequestData['parcel']]));
    }

    public function getSelectedRate()
    {
        $selectedRate = null;
        if($_REQUEST['delivery_goshippo_rate'] && $this->isFullAddress()){
            $rates = $this->getRates();

            foreach ($rates as $rate) {
                if($rate['object_id'] == $_REQUEST['delivery_goshippo_rate']){
                    $selectedRate = $rate;
                }
            }
        }
        return $selectedRate;
    }

}