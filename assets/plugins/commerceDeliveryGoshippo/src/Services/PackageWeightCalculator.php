<?php


namespace CommerceDeliveryGoshippo\Services;


use CommerceDeliveryGoshippo\Helpers;
use Helpers\Config;

class PackageWeightCalculator
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }
    public function calculate($items)
    {
        $volume = 0;
        foreach ($items as $item) {

            $weight = Helpers::getTVValue($this->config->getCFGDef('tv_weight'),$item['id']);

            $volume += $weight * $item['count'];
        }

        return $volume;
    }
}