<?php


namespace CommerceDeliveryGoshippo\Services;


use CommerceDeliveryGoshippo\Helpers;
use Helpers\Config;

class PackageSizeCalculator
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

            $height = Helpers::getTVValue($this->config->getCFGDef('tv_height'),$item['id']);
            $length = Helpers::getTVValue($this->config->getCFGDef('tv_length'),$item['id']);
            $width = Helpers::getTVValue($this->config->getCFGDef('tv_width'),$item['id']);

            $volume += ($height * $length * $width) * $item['count'];
        }

        return intval(pow($volume, 1/3));
    }
}