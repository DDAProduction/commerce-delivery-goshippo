<?php
/** @var DocumentParser $modx */
/** @var array $params */
/** @var \Commerce\Commerce $commerce */


require_once MODX_BASE_PATH . 'assets/plugins/commerceDeliveryGoshippo/autoload.php';

$commerce = ci()->commerce;

$event = $modx->event;
$assets = AssetsHelper::getInstance($modx);
$config = new \Helpers\Config($params);

$deliveryMethodKey = 'goschippo';



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







switch ($event->name) {
    case 'OnInitializeOrderForm':
        /** @var \Commerce\Processors\OrdersProcessor $processor */
        $processor = $modx->commerce->loadProcessor();
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


        $processor = $modx->commerce->loadProcessor();
        $currentDelivery = $processor->getCurrentDelivery();

        $scripts = '';


//        if ($config->getCFGDef('loadAutocomplete')) {
//            $scripts .= $assets->registerScriptsList([
//                'autoComplete.css' => ['src' => 'assets/plugins/commerceDeliveryGoshippo/assets/autoComplete/css/autoComplete.min.css'],
//                'autoComplete.js' => ['src' => 'assets/plugins/commerceDeliveryGoshippo/assets/autoComplete/js/autoComplete.min.js'],
//            ]);
//        }
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

        if ($currentDelivery == $deliveryMethodKey) {


            $countries = $countryRepository->all();
            $selectedCountry = $countryRepository->getSelectedCountryFromRequest($countries);

            $errors = [];
            $rates = [];
            $states = [];
            $selectedState = [];

            if ($selectedCountry && $selectedCountry['require_state']) {
                $stateRepository = new \CommerceDeliveryGoshippo\Repositories\StateRepository($modx, $langCode);
                $states = $stateRepository->getCountryStates($selectedCountry['iso']);
            }


            if (isset($_REQUEST['delivery_goshippo_state'])) {
                foreach ($states as $state) {
                    if ($_REQUEST['delivery_goshippo_state'] == $state['iso']) {
                        $selectedState = $state;
                    }
                }
            }

            $fullName = $_REQUEST[$config->getCFGDef('full_name_field')];
            $canRequestRates = !empty($fullName) && !empty($selectedCountry) && (!empty($states) && !empty($selectedState)) && !empty($_REQUEST['delivery_goshippo_zip'])
                && !empty($_REQUEST['delivery_goshippo_city']) && !empty($_REQUEST['delivery_goshippo_street']);





            $ratesRequestHash = '';

            if ($canRequestRates) {

                $addressToRequest = array_filter([
                    'name' => $fullName,
                    'street1' => $_REQUEST['delivery_goshippo_street'],
                    'city' => $_REQUEST['delivery_goshippo_city'],
                    'state' => $_REQUEST['delivery_goshippo_state'],
                    'zip' => $_REQUEST['delivery_goshippo_zip'],
                    'country' => $_REQUEST['delivery_goshippo_country'],
                ]);

                $cart = ci()->carts->getCart('products');
                $items = $cart->getItems();


                $sideSize = (new \CommerceDeliveryGoshippo\Services\PackageSizeCalculator($config))->calculate($items);
                $weight = (new \CommerceDeliveryGoshippo\Services\PackageWeightCalculator($config))->calculate($items);

                $parcel = array(
                    'length' => $sideSize,
                    'width' => $sideSize,
                    'height' => $sideSize,
                    'distance_unit' => $config->getCFGDef('distance_units'),
                    'weight' => $weight,
                    'mass_unit' => $config->getCFGDef('mass_units'),
                );


                $ratesRequestHash = md5(json_encode(['addressToRequest'=>$addressToRequest,'parcel'=>$parcel]));



                $addressFrom = Shippo_Address::create(array_filter([
                    'name' => $config->getCFGDef('from_name'),
                    'street1' => $config->getCFGDef('from_street1'),
                    'city' => $config->getCFGDef('from_city'),
                    'state' => $config->getCFGDef('from_state'),
                    'zip' => $config->getCFGDef('from_zip'),
                    'country' => $config->getCFGDef('from_country'),
                ]));


                $addressTo = Shippo_Address::create($addressToRequest);




                $shipmentRequest = [
                    'address_from' => $addressFrom,
                    'address_to' => $addressTo,
                    'parcels' => [$parcel],
                    'async' => false
                ];
                $shipment = Shippo_Shipment::create($shipmentRequest);

                $ratesRequest = $shipment->__toArray(true);


                if ($ratesRequest["status"] == "SUCCESS") {
                    $rates = $ratesRequest['rates'];


                } else {
                    $errors = array_merge($errors, $ratesRequest['messages']);
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

//        echo 2;


//        var_dump($_REQUEST['goshippoRates']);
//        die();
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

