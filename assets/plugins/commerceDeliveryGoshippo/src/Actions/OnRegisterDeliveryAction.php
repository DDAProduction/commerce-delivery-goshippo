<?php


namespace CommerceDeliveryGoshippo\Actions;


use Commerce\Commerce;
use CommerceDeliveryGoshippo\Cache;
use CommerceDeliveryGoshippo\Container;
use CommerceDeliveryGoshippo\Factories\ShipmentBuilder;
use CommerceDeliveryGoshippo\Renderer;
use CommerceDeliveryGoshippo\Repositories\CountryRepository;
use CommerceDeliveryGoshippo\Repositories\StateRepository;
use CommerceDeliveryGoshippo\Services\RatesCalculator;
use CommerceDeliveryGoshippo\Shipment;
use Exception;
use Helpers\Config;
use Helpers\Lexicon;

class OnRegisterDeliveryAction
{

    private $deliveryMethodKey;

    /**
     * @var Config
     */
    private $config;
    /**
     * @var \AssetsHelper
     */
    private $assetsHelper;
    /**
     * @var Renderer
     */
    private $renderer;
    /**
     * @var \DocumentParser
     */
    private $modx;
    /**
     * @var CountryRepository
     */
    private $countryRepository;
    /**
     * @var Lexicon
     */
    private $lexicon;

    /**
     * @var StateRepository
     */
    private $stateRepository;
    /**
     * @var \Commerce\Interfaces\Processor|\Commerce\Processors\OrdersProcessor
     */
    private $orderProcessor;
    /**
     * @var RatesCalculator|false|mixed
     */
    private $ratesCalculator;
    /**
     * @var Cache|false|mixed
     */
    private $cache;


    public function __construct(Container $container)
    {


        $this->countryRepository = $container->get(CountryRepository::class);
        $this->stateRepository = $container->get(StateRepository::class);

        $this->config = $container->get(Config::class);

        $this->deliveryMethodKey = $this->config->getCFGDef('deliveryMethodKey');

        $this->assetsHelper = $container->get(\AssetsHelper::class);
        $this->renderer = $container->get(Renderer::class);
        $this->modx = $container->get(\DocumentParser::class);

        $this->lexicon = $container->get(Lexicon::class);
        $this->orderProcessor = ci()->get('commerce')->loadProcessor();

        $this->ratesCalculator = $container->get(RatesCalculator::class);
        $this->cache = $container->get(Cache::class);







    }

    public function handle(&$params){

        $this->registerScripts();

        $shipment = ShipmentBuilder::makeFromFrontRequest();


        $markup = '';
        if ($this->getCurrentDelivery($params) == $this->deliveryMethodKey) {
            $markup = $this->getMarkup($shipment);
        }



        $params['rows'][$this->deliveryMethodKey] = [
            'title' => $this->config->getCFGDef('title', $this->lexicon->get('title')),
            'markup' => $markup,
        ];
    }

    private function getMarkup(Shipment $shipment)
    {
        $errors = [];
        $rates = [];

        $destinationAddress = $shipment->getDestinationAddress();


        $states = [];
        if ($destinationAddress['fields']['country'] && $destinationAddress['requireState']) {
            $states = $this->stateRepository->getCountryStates($destinationAddress['fields']['country']);
        }

        $ratesRequestHash = '';


        if ($destinationAddress['full']) {
            try {
                $rates = $this->getRates($shipment);
            }

            catch (Exception $e){
                $errors[] = $e->getMessage();
            }
        }



        $markupData = [
            'ratesRequestHash' => $ratesRequestHash,

            'countries' => $this->countryRepository->all(),
            'states' => $states,

            'request' => $_REQUEST,

            'errors' => $errors,
            'rates' => $rates,

        ];

        $this->modx->invokeEvent('OnCommerceDeliveryGoshippoBeforeMarkupRender',[
            'data'=>&$markupData,
        ]);


        $markup = $this->renderer->render($this->config->getCFGDef('markup_template'), $markupData);
        return  $this->lexicon->parse($markup);
    }

    private function registerScripts()
    {
        $scripts = '';

        if ($this->config->getCFGDef('loadCss')) {
            $scripts .= $this->assetsHelper->registerScriptsList([
                'goshippo.css' => ['src' => 'assets/plugins/commerceDeliveryGoshippo/assets/goshippo.css'],
            ]);
        }
        if ($this->config->getCFGDef('loadJs')) {
            $scripts .= $this->renderer->render('config.php',[
                'config'=>[
                    'fullNameField' => $this->config->getCFGDef('full_name_field'),
                    'deliveryMethodKey'=>$this->deliveryMethodKey
                ]
            ]);
            $scripts .= $this->assetsHelper->registerScriptsList([
                'goshippo.js' => ['src' => 'assets/plugins/commerceDeliveryGoshippo/assets/script.js'],
            ]);
        }

        $this->modx->regClientHTMLBlock($scripts);
    }

    private function getRates(Shipment $shipmentDto)
    {
        $ratesHash = $shipmentDto->getHash();


        if($this->cache->has($ratesHash)){
            $rates = $this->cache->get($ratesHash);
        }
        else{
            $rates = $this->ratesCalculator->calculator($shipmentDto);

            $this->cache->set($ratesHash,$rates);
        }

        return $rates;
    }

    private function getCurrentDelivery($params)
    {
        $currentDelivery = $this->orderProcessor->getCurrentDelivery();

        if (is_null($currentDelivery) && !is_null($params) &&  empty($params['rows'])) {
            $currentDelivery = $this->config->getCFGDef('deliveryMethodKey');
        }
        return $currentDelivery;

    }

}