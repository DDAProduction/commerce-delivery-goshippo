<?php


namespace CommerceDeliveryGoshippo\Actions;


use Commerce\Processors\OrdersProcessor;
use CommerceDeliveryGoshippo\Container;
use CommerceDeliveryGoshippo\Factories\ShipmentBuilder;
use CommerceDeliveryGoshippo\Services\OldAddressRequest;
use CommerceDeliveryGoshippo\Shipment;
use Helpers\Config;
use Helpers\Lexicon;

class OnCollectSubtotalsAction
{
    private $deliveryMethodKey;
    /**
     * @var OrdersProcessor
     */
    private $ordersProcessor;
    /**
     * @var OldAddressRequest
     */
    private $addressRequest;
    /**
     * @var Lexicon
     */
    private $lexicon;
    /**
     * @var Config
     */
    private $config;

    public function __construct(Container $container)
    {
        $this->config = $container->get(Config::class);
        $this->lexicon = $container->get(Lexicon::class);

        $this->deliveryMethodKey = $this->config->getCFGDef('deliveryMethodKey');
        $this->ordersProcessor = ci()->commerce->loadProcessor();

    }

    public function handle(&$params){
        $currentDelivery = $this->ordersProcessor->getCurrentDelivery();

        $Shipment = ShipmentBuilder::makeFromFrontRequest();


        $selectedRate = $Shipment->getRate();


        if($selectedRate && $currentDelivery == $this->deliveryMethodKey) {
            return;
        }
        $deliveryPrice = ci()->currency->convertToActive($selectedRate['amount'], $selectedRate['currency']);
        if ($this->config->getCFGDef('addDeliveryPriceToTotal')) {
            $params['total'] += $deliveryPrice;
        }
        $params['rows'][$this->deliveryMethodKey] = [
            'title' => $this->lexicon->get('subtotal_title'),
            'price' => $deliveryPrice,
        ];

    }

}