<?php


namespace CommerceDeliveryGoshippo\Actions;


use Commerce\Processors\OrdersProcessor;
use CommerceDeliveryGoshippo\Services\AddressRequest;
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
     * @var AddressRequest
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

    public function __construct($deliveryMethodKey, OrdersProcessor $ordersProcessor, AddressRequest $addressRequest, Lexicon $lexicon, Config $config)
    {
        $this->deliveryMethodKey = $deliveryMethodKey;
        $this->ordersProcessor = $ordersProcessor;
        $this->addressRequest = $addressRequest;
        $this->lexicon = $lexicon;
        $this->config = $config;
    }

    public function handle(&$params){
        $currentDelivery = $this->ordersProcessor->getCurrentDelivery();
        $selectedRate = $this->addressRequest->getSelectedRate();


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