<?php


namespace CommerceDeliveryGoshippo\Actions;


use Commerce\Commerce;
use Commerce\Processors\OrdersProcessor;
use CommerceDeliveryGoshippo\Container;
use CommerceDeliveryGoshippo\Factories\ShipmentBuilder;
use CommerceDeliveryGoshippo\Services\FrontDestinationAddressGetter;
use CommerceDeliveryGoshippo\Repositories\CountryRepository;
use CommerceDeliveryGoshippo\Shipment;
use Helpers\Config;
use Helpers\Lexicon;

class OnInitializeOrderFormAction
{
    /**
     * @var OrdersProcessor
     */
    private $ordersProcessor;

    /**
     * @var Lexicon
     */
    private $lexicon;
    private $deliveryMethodKey;

    /**
     * @var array
     */
    private $address;

    public function __construct(Container $container)
    {

        $this->ordersProcessor = $container->get(Commerce::class)->loadProcessor();

        $this->lexicon = $container->get(Lexicon::class);
        $this->deliveryMethodKey = $container->get(Config::class)->getCFGDef('deliveryMethodKey');



    }

    public function handle(&$params)
    {
        $Shipment = ShipmentBuilder::makeFromFrontRequest();

        $currentDelivery = $this->ordersProcessor->getCurrentDelivery();


        if ($currentDelivery != $this->deliveryMethodKey) {
            return;
        }
        $rules = [
            'delivery_goshippo_country' => [
                'required' => $this->lexicon->get('select_country'),
            ],
            'delivery_goshippo_zip' => [
                'required' => $this->lexicon->get('fill_zip'),
            ],
            'delivery_goshippo_city' => [
                'required' => $this->lexicon->get('fill_city'),
            ],
            'delivery_goshippo_street' => [
                'required' => $this->lexicon->get('fill_street'),
            ],
            'delivery_goshippo_rate_id' => [
                'required' => $this->lexicon->get('select_rate'),
            ],

        ];
        if ($Shipment->getDestinationAddress()['requireState']) {
            $rules['delivery_goshippo_state'] = [
                'required' => $this->lexicon->get('select_state')
            ];
        }
        $params['config']['rules'] = array_merge($params['config']['rules'], $rules);

    }
}