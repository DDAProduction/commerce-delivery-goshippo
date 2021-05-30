<?php
namespace CommerceDeliveryGoshippo\Services\Backend;


use CommerceDeliveryGoshippo\Contacts\RequestAddressProvider;
use CommerceDeliveryGoshippo\Container;
use Helpers\Config;

class BackendDestinationRequestAddressProvider implements RequestAddressProvider
{


    public function __construct(Container $container)
    {

    }

    public function getAddress()
    {
        return [
            'name' => $_REQUEST['name'],
            'country' => isset($_REQUEST['delivery_goshippo_country'])?$_REQUEST['delivery_goshippo_country']:'',
            'state' => isset($_REQUEST['delivery_goshippo_state'])?$_REQUEST['delivery_goshippo_state']:'',
            'city' => isset($_REQUEST['delivery_goshippo_city'])?$_REQUEST['delivery_goshippo_city']:'',
            'street1' => isset($_REQUEST['delivery_goshippo_street'])?$_REQUEST['delivery_goshippo_street']:'',
            'zip' => isset($_REQUEST['delivery_goshippo_zip'])?$_REQUEST['delivery_goshippo_zip']:'',
        ];
    }
}