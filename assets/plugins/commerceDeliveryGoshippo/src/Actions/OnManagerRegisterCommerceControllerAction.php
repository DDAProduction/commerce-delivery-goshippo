<?php


namespace CommerceDeliveryGoshippo\Actions;


use Commerce\Module\Manager;
use CommerceDeliveryGoshippo\Container;
use CommerceDeliveryGoshippo\GoshippoModuleController;

class OnManagerRegisterCommerceControllerAction
{
    /**
     * @var \DocumentParser
     */
    private $modx;

    public function __construct(Container $container)
    {

        $this->modx = $container->get(\DocumentParser::class);
    }

    public function handle(&$params){

        /** @var Manager $module */
        $module = $params['module'];

        $module->registerController('goshippo',new GoshippoModuleController($this->modx, $module));
    }
}