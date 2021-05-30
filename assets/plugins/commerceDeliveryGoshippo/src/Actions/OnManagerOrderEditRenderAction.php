<?php


namespace CommerceDeliveryGoshippo\Actions;


use CommerceDeliveryGoshippo\Container;
use Helpers\Config;

class OnManagerOrderEditRenderAction
{
    /**
     * @var false|Config|mixed
     */
    private $config;
    /**
     * @var \AssetsHelper|false|mixed
     */
    private $assets;
    /**
     * @var \DocumentParser|false|mixed
     */
    private $modx;

    public function __construct(Container $container)
    {
        $this->config = $container->get(Config::class);
        $this->assets = $container->get(\AssetsHelper::class);
        $this->modx = $container->get(\DocumentParser::class);

    }

    public function handle(&$params){
        $jsConfig = [
            'fullNameField' => $this->config->getCFGDef('module_full_name_field'),
            'deliveryMethodKey' => $this->config->getCFGDef('deliveryMethodKey')
        ];

        $scripts = '';
        $scripts .= '<script> var goshippoConfig = ' . json_encode($jsConfig, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';
        $scripts .= $this->assets->registerScriptsList([
            'js' => ['src' => 'assets/plugins/commerceDeliveryGoshippo/assets/module.js',],
        ]);


        $this->modx->event->addOutput($scripts);

    }

}