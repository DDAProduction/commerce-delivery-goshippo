<?php
/** @var $modx DocumentParser */
/** @var $params array */

use Commerce\Commerce;
use CommerceDeliveryGoshippo\Contacts\RequestAddressProvider;
use CommerceDeliveryGoshippo\Contacts\RequestProductsProvider;
use CommerceDeliveryGoshippo\Container;
use CommerceDeliveryGoshippo\Renderer;
use CommerceDeliveryGoshippo\Repositories\CountryRepository;
use CommerceDeliveryGoshippo\Repositories\StateRepository;
use CommerceDeliveryGoshippo\Services\Front\FrontCartProductsGetter;
use CommerceDeliveryGoshippo\Services\Front\FrontDestinationRequestAddressProvider;
use CommerceDeliveryGoshippo\Services\InvoiceCreator;
use CommerceDeliveryGoshippo\Services\RatesCalculator;
use Helpers\Config;
use Helpers\Lexicon;

$container = Container::getInstance();


$container->set(DocumentParser::class,function (Container $container) use($modx){
    return $modx;
});

$container->set(Config::class,function (Container $container) use($params){
    $config = new Config($params);

    $deliveryMethodKey = 'goschippo';
    $showOnlyCountries = !empty($config->getCFGDef('showOnlyCountries')) ? explode(',', $config->getCFGDef('showOnlyCountries')) : [];
    $config->setConfig([
        'deliveryMethodKey'=>$deliveryMethodKey,
        'availableLanguages'=>[
            'english'
        ],
        'showOnlyCountries'=>$showOnlyCountries,
    ]);


    return $config;
});
$container->set(AssetsHelper::class,function (Container $container) use($params){

    return AssetsHelper::getInstance($container->get(DocumentParser::class));
});

$container->set(Commerce::class,function (Container $container) use($params){

    return ci()->commerce;
});




$container->set(Lexicon::class,function (Container $container) use($params){
    $modx = $container->get(DocumentParser::class);
    $availableLanguages = $container->get(Config::class)->getCFGDef('availableLanguages');

    $commerce = $container->get(Commerce::class);


    $lang = in_array($commerce->getCurrentLang(), $availableLanguages) ? $commerce->getCurrentLang() : $availableLanguages[key($availableLanguages)];
    $lexicon = new Lexicon($modx, [
        'lang' => $lang,
        'langDir' => 'assets/plugins/commerceDeliveryGoshippo/lang/'
    ]);
    $lexicon->fromFile('core');

    return $lexicon;
});
$container->set(Renderer::class,function (Container $container) use($params){
    $modx = $container->get(DocumentParser::class);
    $lexicon = $container->get(Lexicon::class);
    $config = $container->get(Config::class);

    switch ($config){

        case 'DLTemplate':
            $renderer = DLTemplate::getInstance($modx);
            break;

        default:
        case 'Default':
            $renderer = new Renderer($modx, $lexicon);
            break;
    }

    return $renderer;
});

$container->set(CountryRepository::class,function (Container $container) use($params){
    $modx = $container->get(DocumentParser::class);
    $lexicon = $container->get(Lexicon::class);
    $config = $container->get(Config::class);


    return new CountryRepository($modx, $lexicon->get('lang_code'), $config->getCFGDef('showOnlyCountries'));
});

$container->set(StateRepository::class,function (Container $container) use($params){
    $modx = $container->get(DocumentParser::class);
    $lexicon = $container->get(Lexicon::class);


    return new StateRepository($modx, $lexicon->get('lang_code'));
});


$container->set(RatesCalculator::class,function (Container $container) {
    return new RatesCalculator($container);
});
$container->set(InvoiceCreator::class,function (Container $container) {
    return new InvoiceCreator($container);
});

$container->set(\CommerceDeliveryGoshippo\Cache::class,function (Container $ci) {
    return new \CommerceDeliveryGoshippo\Cache($ci);
});


$container->set(\Helpers\FS::class,function (Container $container) {
    return \Helpers\FS::getInstance();
});
