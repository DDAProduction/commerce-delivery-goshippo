<?php


namespace CommerceDeliveryGoshippo\Services;


use CommerceDeliveryGoshippo\Container;
use Helpers\Config;

class AddressSenderGetter
{

    /**
     * @var false|Config|mixed
     */
    private $config;
    /**
     * @var \DocumentParser|false|mixed
     */
    private $modx;

    public function __construct(Container $container)
    {
        $this->config = $container->get(Config::class);
        $this->modx = $container->get(\DocumentParser::class);
    }

    public function getAddress(){



        $address = [
            'fields' => array_filter([
                'name' => $this->config->getCFGDef('from_name'),
                'country' => $this->config->getCFGDef('from_country'),
                'state' => $this->config->getCFGDef('from_state'),
                'city' => $this->config->getCFGDef('from_city'),
                'street1' => $this->config->getCFGDef('from_street1'),
                'zip' => $this->config->getCFGDef('from_zip'),
            ]),
            'full' => true,
            'requireState' => !empty($this->config->getCFGDef('from_state'))
        ];


        $this->modx->invokeEvent('OnCommerceDeliveryGoshippoAddressReceived',[
            'address'=>&$address,
            'type'=> 'sender'
        ]);

        return $address;
    }
}