<?php
namespace CommerceDeliveryGoshippo;


use Commerce\Commerce;
use Commerce\Module\Controllers\Controller;
use Commerce\Processors\OrdersProcessor;
use Shippo_Transaction;

class GoshippoController extends Controller implements \Commerce\Module\Interfaces\Controller
{
    private $commerce;
    /** @var $orderProcessor OrdersProcessor */
    private $orderProcessor;
    /**
     * @var \Helpers\Lexicon
     */
    private $lexicon;

    public function __construct($modx, $module)
    {
        parent::__construct($modx, $module);

        /** @var Commerce $commerce */
        $this->commerce = $this->modx->commerce;
        $this->orderProcessor = $this->commerce->loadProcessor();
        $this->lexicon =\CommerceDeliveryGoshippo\Factories\LexiconFactory::build();
    }

    public function index()
    {

        $this->orderProcessor->loadOrder($_GET['order_id']);
        $order = $this->orderProcessor->getOrder();

        $rate = json_decode($order['fields']['goshippo']['rate'],true);
        if(empty($rate)){
            $this->module->sendRedirectBack(['error' => $this->lexicon->get('select_rate')]);
        }


        try {

            $transaction = Shippo_Transaction::create([
                'rate' => $rate['object_id'],
                'label_file_type' => "PDF",
                'async' => false
            ])->__toArray(true);


            if ($transaction["status"] != "SUCCESS") {
                throw new \Exception('<pre>'.print_r($transaction["messages"],true).'</pre>');
            }

            $order['fields']['goshippo']['transaction'] = $transaction;
            $this->orderProcessor->updateOrder($order['id'],['values'=>$order]);
            $this->modx->logEvent(1, 1, 'Goshippo create success <br>' . print_r($transaction, true), 'Goshippo');
            $this->module->sendRedirectBack(['success' => $this->lexicon->get('invoice_create')]);

        }
        catch (\Exception $e){

            $this->modx->logEvent(1, 3, $e->getMessage(), 'Goshippo');
            $this->module->sendRedirectBack(['error' => $e->getMessage()]);

        }









    }
}