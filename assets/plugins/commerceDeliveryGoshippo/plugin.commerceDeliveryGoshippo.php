<?php
/** @var DocumentParser $modx */
/** @var array $params */
/** @var \Commerce\Commerce $commerce */


use CommerceDeliveryGoshippo\Services\AddressRequest;

require_once MODX_BASE_PATH . 'assets/plugins/commerceDeliveryGoshippo/autoload.php';

$commerce = ci()->commerce;

$event = $modx->event;
$assets = AssetsHelper::getInstance($modx);
$config = new \Helpers\Config($params);

$deliveryMethodKey = 'goschippo';



$cache = new \CommerceDeliveryGoshippo\Cache();


$availableLanguages = [
    'english'
];


$lang = in_array($commerce->getCurrentLang(),$availableLanguages)?$commerce->getCurrentLang():$availableLanguages[key($availableLanguages)];
$lexicon = new \Helpers\Lexicon($modx, [
    'lang' => $lang,
    'langDir' => 'assets/plugins/commerceDeliveryGoshippo/lang/'
]);
$lexicon->fromFile('core');

$langCode = $lexicon->get('lang_code');

$render = new \CommerceDeliveryGoshippo\Renderer($modx, $langCode);

Shippo::setApiKey($config->getCFGDef('goshippo_token'));



$showOnlyCountries = !empty($config->getCFGDef('showOnlyCountries')) ? explode(',', $config->getCFGDef('showOnlyCountries')) : [];
$countryRepository = new \CommerceDeliveryGoshippo\Repositories\CountryRepository($modx, $langCode, $showOnlyCountries);
$stateRepository = new \CommerceDeliveryGoshippo\Repositories\StateRepository($modx, $langCode);


$addressRequest = new AddressRequest($cache,$config,$countryRepository,$stateRepository, $_REQUEST);


/** @var \Commerce\Processors\OrdersProcessor $processor */
$processor = $modx->commerce->loadProcessor();

switch ($event->name) {
    case 'OnInitializeOrderForm':

        $currentDelivery = $processor->getCurrentDelivery();
        $selectedCountry = $countryRepository->getSelectedCountryFromRequest();


        if($currentDelivery == $deliveryMethodKey){
            $rules = [
                'delivery_goshippo_country'=>[
                    'required'=> $lexicon->get('select_country'),
                ],
                'delivery_goshippo_zip'=>[
                    'required'=> $lexicon->get('fill_zip'),
                ],
                'delivery_goshippo_city'=>[
                    'required'=> $lexicon->get('fill_city'),
                ],
                'delivery_goshippo_street'=>[
                    'required'=> $lexicon->get('fill_street'),
                ],
                'delivery_goshippo_rate'=>[
                    'required'=> $lexicon->get('select_rate'),
                ],

            ];
            if($selectedCountry['require_state']){
                $rules['delivery_goshippo_state'] = [
                    'required'=>$lexicon->get('select_state')
                ];
            }
            $params['config']['rules'] = array_merge($params['config']['rules'],$rules);
        }

        break;

    case 'OnRegisterDelivery':

        $start = microtime(true);

        $processor = $modx->commerce->loadProcessor();
        $currentDelivery = $processor->getCurrentDelivery();

        if(is_null($currentDelivery) && empty($rows)){
            $currentDelivery = $deliveryMethodKey;
        }

        $scripts = '';

        if ($config->getCFGDef('loadCss')) {
            $scripts .= $assets->registerScriptsList([
                'goshippo.css' => ['src' => 'assets/plugins/commerceDeliveryGoshippo/assets/goshippo.css'],
            ]);
        }
        if ($config->getCFGDef('loadJs')) {
            $scripts .= $render->render('config.php',[
                'config'=>[
                    'fullNameField' => $config->getCFGDef('full_name_field'),
                    'deliveryMethodKey'=>$deliveryMethodKey
                ]
            ]);
            $scripts .= $assets->registerScriptsList([
                'goshippo.js' => ['src' => 'assets/plugins/commerceDeliveryGoshippo/assets/script.js'],
            ]);
        }

        $modx->regClientHTMLBlock($scripts);


        $price = 0;
        $markup = '';
        $errors = [];
        $rates = [];
        if ($currentDelivery == $deliveryMethodKey) {

            $countries = $countryRepository->all();
            $selectedCountry = $addressRequest->getSelectedCountry();

            $states = [];
            if ($selectedCountry && $selectedCountry['require_state']) {
                $states = $stateRepository->getCountryStates($selectedCountry['iso']);
            }

            $selectedState = $addressRequest->getSelectedState();


            $ratesRequestHash = '';


            if($addressRequest->isFullAddress()){

                $ratesRequestHash = $addressRequest->getRateRequestHash();


                try {
                    $rates = $addressRequest->getRates();
                }
                catch (Exception $e){
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


            $markup = $render->render('markup.php', $markupData);
            $markup = $lexicon->parse($markup);

        }


        $params['rows'][$deliveryMethodKey] = [
            'title' => $config->getCFGDef('title', $lexicon->get('title')),
            'markup' => $markup,
            'price' => $price,
        ];


        break;
    case 'OnCollectSubtotals':

        $currentDelivery = $processor->getCurrentDelivery();
        $selectedRate = $addressRequest->getSelectedRate();


        if($selectedRate && $currentDelivery == $deliveryMethodKey){


            $deliveryPrice = ci()->currency->convertToActive($selectedRate['amount'], $selectedRate['currency']);
            if($config->getCFGDef('addDeliveryPriceToTotal')){
            $params['total'] += $deliveryPrice;
            }
            $params['rows'][$deliveryMethodKey] = [
                'title' => $lexicon->get('subtotal_title'),
                'price' => $deliveryPrice,
            ];
            break;

        }
        break;
    case 'OnBeforeOrderProcessing':

        $currentDelivery = $processor->getCurrentDelivery();
        $selectedRate = $addressRequest->getSelectedRate();

        if($currentDelivery == $deliveryMethodKey && $selectedRate ){

            // Purchase the desired rate.
            $transaction = Shippo_Transaction::create( [
                'rate' => $selectedRate["object_id"],
                'label_file_type' => "PDF",
                'async' => false
            ])->__toArray();


            if ($transaction["status"] == "SUCCESS"){

                $FL->setField('goshippo', [
                    'label_url'=> $transaction["label_url"],
                    'tracking_number'=> $transaction["tracking_number"],
                    'tracking_url_provider'=> $transaction["tracking_url_provider"],
                ]);

                $modx->logEvent(1,1,'Goshippo create success <br>'.print_r($transaction,true),'Goshippo');


            }else {
                $modx->logEvent(1,3,print_r($transaction["messages"],true),'Goshippo');

            }




        }
        break;
    case 'OnManagerBeforeOrderRender':


        if($params['order']['fields']){

            $params['groups']['payment_delivery']['fields']['label_url'] = [
                'title' => $lexicon->get('label_url'),
                'content' => function ($data) {
                    return '<a target="_blank" href=' . $data['fields']['goshippo']['label_url'] . '>Follow</a>';
                },
                'sort' => 21,
            ];

            $params['groups']['payment_delivery']['fields']['tracking_number'] = [
                'title' => $lexicon->get('https://monosnap.com/direct/o95Px9Hc8ovUrYexJwyMRrFFAi7frr'),
                'content' => function ($data) {

                    return $data['fields']['goshippo']['tracking_number'];
                },
                'sort' => 22,
            ];

            $params['groups']['payment_delivery']['fields']['tracking_url_provider'] = [
                'title' => $lexicon->get('tracking_url_provider'),
                'content' => function ($data) {
                    return '<a target="_blank" href=' . $data['fields']['goshippo']['tracking_url_provider'] . '>Follow</a>';
                },
                'sort' => 23,
            ];

        }

        break;


    case 'OnPageNotFound':
        switch ($_GET['q']){
            case 'ajax/commerce/delivery/goshippo/states':
                $stateRepository = new \CommerceDeliveryGoshippo\Repositories\StateRepository($modx, $langCode);
                header('Content-type: text/json');
                echo json_encode($stateRepository->getCountryStates($_REQUEST['country']));
                die();
        }
        break;
}

