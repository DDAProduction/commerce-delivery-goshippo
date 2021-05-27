<?php


namespace CommerceDeliveryGoshippo\Actions;


use Commerce\Module\Manager;
use CommerceDeliveryGoshippo\GoshippoController;

class OnManagerRegisterCommerceControllerAction
{
    /**
     * @var \DocumentParser
     */
    private $modx;

    public function __construct(\DocumentParser $modx)
    {

        $this->modx = $modx;
    }

    public function handle(&$params){

        /** @var Manager $module */
        $module = $params['module'];

        $module->registerController('goshippo',new GoshippoController($this->modx, $module));
    }
}