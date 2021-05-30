<?php
namespace CommerceDeliveryGoshippo\Services;


use CommerceDeliveryGoshippo\Container;
use Shippo_Transaction;

class InvoiceCreator
{
    /**
     * @var \DocumentParser|false|mixed
     */
    private $modx;
    /**
     * @var \Commerce\Interfaces\Processor|\Commerce\Processors\OrdersProcessor
     */
    private $orderProcessor;

    public function __construct(Container $container)
    {
        $this->modx = $container->get(\DocumentParser::class);
        $this->orderProcessor = ci()->commerce->loadProcessor();

    }

    public function createInvoice($rate,$order){

        $prevent = false;

        $requestParams = [
            'rate' => $rate["object_id"],
            'label_file_type' => "PDF",
            'async' => false
        ];

        $this->modx->invokeEvent('OnCommerceDeliveryGoshippoInvoiceCreate',[
            'order'=>$order,
            'rate'=>$rate,
            'request_params'=>&$requestParams,
            'prevent' => &$prevent
        ]);

        if($prevent){
            return ;
        }

        $transaction = Shippo_Transaction::create($requestParams)->__toArray(true);

        if($transaction["status"] !== "SUCCESS"){
            $this->modx->logEvent(1, 3, print_r($transaction["messages"], true), 'Goshippo');
            throw new \Exception('Can not create invoice');
        }

        $this->orderProcessor->updateOrder($order['id'],['values'=>['fields'=>['delivery_goshippo_transaction'=>$transaction]]]);

        $updatedOrder = $this->orderProcessor->loadOrder($order['id'],true);

        $this->modx->invokeEvent('OnCommerceDeliveryGoshippoInvoiceCreate',[
            'rate'=>$rate,
            'order'=>$updatedOrder,
            'transaction'=>$transaction
        ]);

        $this->modx->logEvent(1, 1, 'Goshippo create success <br>' . print_r($transaction, true), 'Goshippo');
    }
}