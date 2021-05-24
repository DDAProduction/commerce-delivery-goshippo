<?php


namespace CommerceDeliveryGoshippo\Actions;


use Commerce\Processors\OrdersProcessor;
use CommerceDeliveryGoshippo\Renderer;
use CommerceDeliveryGoshippo\Repositories\CountryRepository;
use CommerceDeliveryGoshippo\Repositories\StateRepository;
use CommerceDeliveryGoshippo\Services\AddressRequest;
use Exception;
use Helpers\Config;
use Helpers\Lexicon;

class OnRegisterDeliveryAction
{

    private $deliveryMethodKey;
    /**
     * @var OrdersProcessor
     */
    private $ordersProcessor;
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
     * @var AddressRequest
     */
    private $addressRequest;
    /**
     * @var StateRepository
     */
    private $stateRepository;

    public function __construct($deliveryMethodKey, OrdersProcessor $ordersProcessor, Config $config, \AssetsHelper $assetsHelper, Renderer $renderer, \DocumentParser $modx, CountryRepository $countryRepository, Lexicon $lexicon, AddressRequest $addressRequest,StateRepository $stateRepository)
    {
        $this->deliveryMethodKey = $deliveryMethodKey;
        $this->ordersProcessor = $ordersProcessor;
        $this->config = $config;
        $this->assetsHelper = $assetsHelper;
        $this->renderer = $renderer;
        $this->modx = $modx;
        $this->countryRepository = $countryRepository;
        $this->lexicon = $lexicon;
        $this->addressRequest = $addressRequest;
        $this->stateRepository = $stateRepository;
    }

    public function handle(&$params){

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


        $price = 0;
        $markup = $this->getMarkup();




        $params['rows'][$this->deliveryMethodKey] = [
            'title' => $this->config->getCFGDef('title', $this->lexicon->get('title')),
            'markup' => $markup,
            'price' => $price,
        ];
    }

    private function getMarkup()
    {
        $errors = [];
        $rates = [];

        $currentDelivery = $this->ordersProcessor->getCurrentDelivery();

        if (is_null($currentDelivery) && empty($params['rows'])) {
            $currentDelivery = $this->deliveryMethodKey;
        }

        if ($currentDelivery != $this->deliveryMethodKey) {
            return '';
        }
        $countries = $this->countryRepository->all();
        $selectedCountry = $this->addressRequest->getSelectedCountry();

        $states = [];
        if ($selectedCountry && $selectedCountry['require_state']) {
            $states = $this->stateRepository->getCountryStates($selectedCountry['iso']);
        }

        $selectedState = $this->addressRequest->getSelectedState();


        $ratesRequestHash = '';


        if ($this->addressRequest->isFullAddress()) {

            $ratesRequestHash = $this->addressRequest->getRateRequestHash();


            try {
                $rates = $this->addressRequest->getRates();
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }


        $markupData = [
            'ratesRequestHash' => $ratesRequestHash,
            'countries' => $countries,
            'selectedCountry' => $selectedCountry,

            'states' => $states,
            'selectedState' => $selectedState,
            'request' => $_REQUEST,

            'errors' => $errors,
            'rates' => $rates,

        ];


        $markup = $this->renderer->render('markup.php', $markupData);
        return  $this->lexicon->parse($markup);
    }

}