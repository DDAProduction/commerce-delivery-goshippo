<?php

namespace CommerceDeliveryGoshippo\Actions;


use Commerce\Processors\OrdersProcessor;
use CommerceDeliveryGoshippo\Services\AddressRequest;
use Shippo_Transaction;

class OnBeforeOrderProcessingAction
{
    /**
     * @var OrdersProcessor
     */
    private $ordersProcessor;
    private $deliveryMethodKey;
    /**
     * @var \DocumentParser
     */
    private $modx;

    public function __construct($deliveryMethodKey, OrdersProcessor $ordersProcessor, \DocumentParser $modx)
    {
        $this->ordersProcessor = $ordersProcessor;
        $this->deliveryMethodKey = $deliveryMethodKey;
        $this->modx = $modx;
    }

    public function handle(array &$params, AddressRequest $addressRequest)
    {
        $currentDelivery = $this->ordersProcessor->getCurrentDelivery();
        $selectedRate = $addressRequest->getSelectedRate();

        if ($currentDelivery != $this->deliveryMethodKey) {

            return;
        }



        $transaction = Shippo_Transaction::create([
            'rate' => $selectedRate["object_id"],
            'label_file_type' => "PDF",
            'async' => false
        ])->__toArray(true);


        if ($transaction["status"] == "SUCCESS") {


            $params['FL']->setField('goshippo', [
                'rate' => $selectedRate,
                'transaction' => $transaction
            ]);


            $this->modx->logEvent(1, 1, 'Goshippo create success <br>' . print_r($transaction, true), 'Goshippo');


        } else {
            $this->modx->logEvent(1, 3, print_r($transaction["messages"], true), 'Goshippo');

        }

    }
}