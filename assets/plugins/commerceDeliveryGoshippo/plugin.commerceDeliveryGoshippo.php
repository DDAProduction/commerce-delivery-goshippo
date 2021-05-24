<?php

/** @var DocumentParser $modx */
/** @var array $params */

/** @var Commerce $commerce */

use Commerce\Commerce;
use Commerce\Processors\OrdersProcessor;
use CommerceDeliveryGoshippo\Actions\OnInitializeOrderFormAction;
use CommerceDeliveryGoshippo\Cache;
use CommerceDeliveryGoshippo\Renderer;
use CommerceDeliveryGoshippo\Repositories\CountryRepository;
use CommerceDeliveryGoshippo\Repositories\StateRepository;
use CommerceDeliveryGoshippo\Services\AddressRequest;
use Helpers\Config;
use Helpers\Lexicon;

require_once MODX_BASE_PATH . 'assets/plugins/commerceDeliveryGoshippo/autoload.php';

$commerce = ci()->commerce;

$event = $modx->event;
$assets = AssetsHelper::getInstance($modx);
$config = new Config($params);

$deliveryMethodKey = 'goschippo';


$cache = new Cache();


$availableLanguages = [
    'english'
];


$lang = in_array($commerce->getCurrentLang(), $availableLanguages) ? $commerce->getCurrentLang() : $availableLanguages[key($availableLanguages)];
$lexicon = new Lexicon($modx, [
    'lang' => $lang,
    'langDir' => 'assets/plugins/commerceDeliveryGoshippo/lang/'
]);
$lexicon->fromFile('core');

$langCode = $lexicon->get('lang_code');

$render = new Renderer($modx, $lexicon);

Shippo::setApiKey($config->getCFGDef('goshippo_token'));


$showOnlyCountries = !empty($config->getCFGDef('showOnlyCountries')) ? explode(',', $config->getCFGDef('showOnlyCountries')) : [];
$countryRepository = new CountryRepository($modx, $langCode, $showOnlyCountries);
$stateRepository = new StateRepository($modx, $langCode);


$addressRequest = new AddressRequest($cache, $config, $countryRepository, $stateRepository, $_REQUEST);


/** @var OrdersProcessor $orderProcessor */
$orderProcessor = $modx->commerce->loadProcessor();

switch ($event->name) {
    case 'OnInitializeOrderForm':

        $action = new OnInitializeOrderFormAction($deliveryMethodKey, $orderProcessor, $countryRepository, $lexicon);
        $action->handle($params);

        break;

    case 'OnRegisterDelivery':

        $action = new \CommerceDeliveryGoshippo\Actions\OnRegisterDeliveryAction($deliveryMethodKey, $orderProcessor, $config, $assets, $render, $modx, $countryRepository, $lexicon, $addressRequest, $stateRepository);
        $action->handle($params);

        break;
    case 'OnCollectSubtotals':
        $action = new \CommerceDeliveryGoshippo\Actions\OnCollectSubtotalsAction($deliveryMethodKey, $orderProcessor, $addressRequest, $lexicon, $config);
        $action->handle($params);
        break;
    case 'OnBeforeOrderProcessing':

        $action = new \CommerceDeliveryGoshippo\Actions\OnBeforeOrderProcessingAction($deliveryMethodKey, $orderProcessor, $modx);
        $action->handle($params, $addressRequest);

        break;
    case 'OnManagerBeforeOrderRender':

        $action = new \CommerceDeliveryGoshippo\Actions\OnManagerBeforeOrderRenderAction($lexicon);
        $action->handle($params);
        break;

    case 'OnManagerBeforeOrderEditRender':

        $action = new \CommerceDeliveryGoshippo\Actions\OnManagerBeforeOrderEditRenderAction($lexicon, $modx, $render, $showOnlyCountries);
        $action->handle($params);

        break;
    case 'OnManagerOrderEditRender':
        $jsConfig = [
            'fullNameField' => $config->getCFGDef('module_full_name_field'),
            'deliveryMethodKey' => $deliveryMethodKey


        ];

        $scripts = '';
        $scripts .= '<script> var goshippoConfig = ' . json_encode($jsConfig, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';
        $scripts .= $assets->registerScriptsList([
            'js' => ['src' => 'assets/plugins/commerceDeliveryGoshippo/assets/module.js',],
        ]);


        $modx->event->addOutput($scripts);


        break;

    case 'OnPageNotFound':
        if (!preg_match('~^ajax/commerce/delivery/goshippo/(.*)$~', $_GET['q'], $matches)) {
            return true;
        }
        $action = $matches[1];

        $response = [];

        switch ($action) {
            case 'states':
                $stateRepository = new StateRepository($modx, $langCode);
                $response = $stateRepository->getCountryStates($_REQUEST['country']);
                break;
            case 'rates-calculate':
                $addressRequest = new AddressRequest($cache,$config,$countryRepository,$stateRepository,$_REQUEST);
                $addressRequest->setFullNameField('name');
                $addressRequest->setCartItems($_REQUEST['cart']);
                try {
                    $response = ['status'=>true,'rates'=>$addressRequest->getRates()];
                }
                catch (Exception $exception){
                    $response = ['status'=>false,'message'=>$exception->getMessage()];
                }
                break;
        }

        if(is_array($response)){
            header('Content-type: text/json');
            echo json_encode($response);
        }
        else{
            echo $response;
        }


        die();


        break;
}

