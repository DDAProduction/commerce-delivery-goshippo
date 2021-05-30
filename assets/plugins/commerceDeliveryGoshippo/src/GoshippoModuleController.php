<?php
namespace CommerceDeliveryGoshippo;


use Commerce\Commerce;
use Commerce\Module\Controllers\Controller;
use Commerce\Processors\OrdersProcessor;
use CommerceDeliveryGoshippo\Services\InvoiceCreator;
use Helpers\Lexicon;
use Shippo_Transaction;

class GoshippoModuleController extends Controller implements \Commerce\Module\Interfaces\Controller
{
    private $commerce;
    /** @var $orderProcessor OrdersProcessor */
    private $orderProcessor;
    /**
     * @var \Helpers\Lexicon
     */
    private $lexicon;
    /**
     * @var InvoiceCreator|false|mixed
     */
    private $invoiceCreator;

    public function __construct($modx, $module)
    {
        parent::__construct($modx, $module);

        $container = Container::getInstance();

        /** @var Commerce $commerce */
        $this->commerce = $this->modx->commerce;
        $this->orderProcessor = $this->commerce->loadProcessor();
        $this->lexicon =$container->get(Lexicon::class);
        $this->invoiceCreator = $container->get(InvoiceCreator::class);
    }

    public function index()
    {

        $this->orderProcessor->loadOrder($_GET['order_id']);
        $order = $this->orderProcessor->getOrder();

        $rate = json_decode($order['fields']['delivery_goshippo_rate'],true);

        if(empty($rate)){
            $this->module->sendRedirectBack(['error' => $this->lexicon->get('select_rate')]);
        }

        try {
            $this->invoiceCreator->createInvoice($rate,$order);
            $this->module->sendRedirectBack(['success' => $this->lexicon->get('invoice_create')]);

        }
        catch (\Exception $e){
            $this->module->sendRedirectBack(['error' => $e->getMessage()]);
        }
    }
}