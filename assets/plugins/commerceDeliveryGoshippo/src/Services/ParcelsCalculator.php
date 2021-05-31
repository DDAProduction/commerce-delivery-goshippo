<?php


namespace CommerceDeliveryGoshippo\Services;


use CommerceDeliveryGoshippo\Contacts\RequestProductsProvider;
use CommerceDeliveryGoshippo\Container;
use CommerceDeliveryGoshippo\Helpers;
use Helpers\Config;

class ParcelsCalculator
{
    /**
     * @var Config
     */
    private $config;
    /**
     * @var RequestProductsProvider
     */
    private $productsProvider;
    /**
     * @var \DocumentParser|false|mixed
     */
    private $modx;

    public function __construct(Container $container)
    {
        $this->config = $container->get(Config::class);
        $this->modx = $container->get(\DocumentParser::class);

    }
    public function calculate($items)
    {
        $totalVolume = 0;
        $totalWeight = 0;
        foreach ($items as $item) {

            $height = Helpers::getTVValue($this->config->getCFGDef('tv_height'),$item['id']);
            $length = Helpers::getTVValue($this->config->getCFGDef('tv_length'),$item['id']);
            $width = Helpers::getTVValue($this->config->getCFGDef('tv_width'),$item['id']);

            $totalVolume += ($height * $length * $width) * $item['count'];

            $weight = Helpers::getTVValue($this->config->getCFGDef('tv_weight'),$item['id']);

            $totalWeight += $weight * $item['count'];
        }

        $totalVolume = intval(pow($totalVolume, 1/3));
        $parcel = array(
            'length' => $totalVolume,
            'width' => $totalVolume,
            'height' => $totalVolume,
            'distance_unit' => $this->config->getCFGDef('distance_units'),
            'weight' => $totalWeight,
            'mass_unit' => $this->config->getCFGDef('mass_units'),
        );
        $parcels = [$parcel];


        $this->modx->invokeEvent('OnCommerceDeliveryGoshippoParcelsCalculate',[
            'items'=> $items,
            'parcels'=>&$parcels,
        ]);

        return $parcels;
    }


}