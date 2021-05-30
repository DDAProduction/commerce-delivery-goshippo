<?php

namespace CommerceDeliveryGoshippo;


use CommerceDeliveryGoshippo\Factories\ShipmentBuilder;
use CommerceDeliveryGoshippo\Repositories\StateRepository;
use CommerceDeliveryGoshippo\Services\RatesCalculator;
use Helpers\Config;
use Helpers\Lexicon;

class GoshippoController
{

    /**
     * @var \DocumentParser|false|mixed
     */
    private $modx;
    /**
     * @var false|Lexicon|mixed
     */
    private $lexicon;
    /**
     * @var StateRepository
     */
    private $stateRepository;
    /**
     * @var RatesCalculator|false|mixed
     */
    private $ratesCalculator;

    public function __construct(Container $container)
    {
        $this->modx = $container->get(\DocumentParser::class);
        $this->lexicon = $container->get(Lexicon::class);

        $this->stateRepository = new StateRepository($this->modx, $this->lexicon->get('lang_code'));
        $this->ratesCalculator = $container->get(RatesCalculator::class);
    }



    public function states($request)
    {

        return [
            'states'=>$this->stateRepository->getCountryStates($request['country'])
        ];
    }

    public function ratesCalculate()
    {
        $shipment = ShipmentBuilder::makeFromBackendRequest();
        $rates = $this->ratesCalculator->calculator($shipment);


        return [
            'rates' => $rates
        ];

    }
}