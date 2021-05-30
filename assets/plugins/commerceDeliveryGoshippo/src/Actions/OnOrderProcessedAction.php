<?php

namespace CommerceDeliveryGoshippo\Actions;


use Commerce\Processors\OrdersProcessor;
use CommerceDeliveryGoshippo\Container;
use CommerceDeliveryGoshippo\Factories\ShipmentBuilder;
use CommerceDeliveryGoshippo\Services\InvoiceCreator;
use CommerceDeliveryGoshippo\Services\OldAddressRequest;
use CommerceDeliveryGoshippo\Shipment;
use Helpers\Config;
use Shippo_Transaction;

class OnOrderProcessedAction
{
    /**
     * @var OrdersProcessor
     */
    private $ordersProcessor;
    private $deliveryMethodKey;

    /**
     * @var InvoiceCreator
     */
    private $invoiceCreator;

    public function __construct(Container $container)
    {
        $this->ordersProcessor = ci()->commerce->loadProcessor();
        $this->deliveryMethodKey = $container->get(Config::class)->getCFGDef('deliveryMethodKey');
        $this->invoiceCreator = new InvoiceCreator($container);

    }

    public function handle(array &$params)
    {
        $currentDelivery = $this->ordersProcessor->getCurrentDelivery();

        $shipment = ShipmentBuilder::makeFromFrontRequest();
        $selectedRate = $shipment->getRate();

        if ($currentDelivery != $this->deliveryMethodKey) {
            return;
        }

        $order = $params['order'];

        try {
            $this->invoiceCreator->createInvoice($selectedRate,$order);
        } catch (\Exception $exception) {

        }

        $this->ordersProcessor->updateOrder($order['id'],['values'=>['fields'=>['delivery_goshippo_rate'=>$selectedRate]]]);
        $this->ordersProcessor->loadOrder($order['id'],true);

    }
}