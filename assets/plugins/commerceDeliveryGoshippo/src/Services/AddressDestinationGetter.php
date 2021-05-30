<?php


namespace CommerceDeliveryGoshippo\Services;


use CommerceDeliveryGoshippo\Contacts\RequestAddressProvider;
use CommerceDeliveryGoshippo\Container;
use CommerceDeliveryGoshippo\Repositories\CountryRepository;

class AddressDestinationGetter
{

    /**
     * @var CountryRepository|false|mixed
     */
    private $countryRepository;
    /**
     * @var CountryRepository|false|mixed
     */
    private $modx;

    public function __construct(Container $container)
    {
        $this->countryRepository = $container->get(CountryRepository::class);
        $this->modx = $container->get(\DocumentParser::class);

    }

    public function getAddress($requestAddress)
    {



        $name = $requestAddress['name'];
        $country = $requestAddress['country'];
        $state = $requestAddress['state'];
        $city = $requestAddress['city'];
        $street1 = $requestAddress['street1'];
        $zip = $requestAddress['zip'];

        $requireState = $this->countryRepository->requireState($country);

        $isFull =
            !empty($name) &&
            !empty($country) &&
            ($requireState == 0 || !empty($state)) &&
            !empty($zip) &&
            !empty($city) &&
            !empty($street1);




        $address = [
            'fields' => [
                'name' => $name,
                'country' => $country,
                'state' => $state,
                'city' => $city,
                'street1' => $street1,
                'zip' => $zip,
            ],
            'full' => $isFull,
            'requireState' => $requireState
        ];

        $this->modx->invokeEvent('OnCommerceDeliveryGoshippoAddressReceived',[
            'address'=>&$address,
            'type'=> 'destination'
        ]);

        return $address;
    }
}