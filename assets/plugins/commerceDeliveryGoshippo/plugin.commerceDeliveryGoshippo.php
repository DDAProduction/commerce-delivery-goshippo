<?php

/** @var DocumentParser $modx */
/** @var array $params */

use CommerceDeliveryGoshippo\Container;
use Helpers\Config;

require_once MODX_BASE_PATH . 'assets/plugins/commerceDeliveryGoshippo/autoload.php';
require_once MODX_BASE_PATH . 'assets/plugins/commerceDeliveryGoshippo/dependencies.php';

$event = $modx->event;
$container = Container::getInstance();




Shippo::setApiKey($container->get(Config::class)->getCFGDef('goshippo_token'));
$orderProcessor = ci()->commerce->loadProcessor();


if(class_exists($class = '\CommerceDeliveryGoshippo\Actions\\'.$event->name.'Action')){
    $action = new $class($container);
    call_user_func_array([$action,'handle'],[&$params]);

}