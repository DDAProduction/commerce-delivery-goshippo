<?php
namespace CommerceDeliveryGoshippo\Services\Front;


use CommerceDeliveryGoshippo\Contacts\RequestAddressProvider;
use CommerceDeliveryGoshippo\Container;
use CommerceDeliveryGoshippo\Repositories\CountryRepository;
use Helpers\Config;

class FrontDestinationRequestAddressProvider implements RequestAddressProvider
{

    /**
     * @var false|Config|mixed
     */
    private $config;

    public function __construct(Container $container)
    {
        $this->config = $container->get(Config::class);

    }

    public function getAddress()
    {
        return [
            'name' => $_REQUEST[$this->config->getCFGDef('full_name_field')],
            'country' => isset($_REQUEST['delivery_goshippo_country'])?$_REQUEST['delivery_goshippo_country']:'',
            'state' => isset($_REQUEST['delivery_goshippo_state'])?$_REQUEST['delivery_goshippo_state']:'',
            'city' => isset($_REQUEST['delivery_goshippo_city'])?$_REQUEST['delivery_goshippo_city']:'',
            'street1' => isset($_REQUEST['delivery_goshippo_street'])?$_REQUEST['delivery_goshippo_street']:'',
            'zip' => isset($_REQUEST['delivery_goshippo_zip'])?$_REQUEST['delivery_goshippo_zip']:'',
        ];
    }
}