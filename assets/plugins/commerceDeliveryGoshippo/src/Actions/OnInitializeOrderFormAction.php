<?php


namespace CommerceDeliveryGoshippo\Actions;


use Commerce\Processors\OrdersProcessor;
use CommerceDeliveryGoshippo\Repositories\CountryRepository;
use Helpers\Lexicon;

class OnInitializeOrderFormAction
{
    /**
     * @var OrdersProcessor
     */
    private $ordersProcessor;
    /**
     * @var CountryRepository
     */
    private $countryRepository;
    /**
     * @var Lexicon
     */
    private $lexicon;
    private $deliveryMethodKey;

    public function __construct($deliveryMethodKey, OrdersProcessor $ordersProcessor, CountryRepository $countryRepository, Lexicon $lexicon)
    {

        $this->ordersProcessor = $ordersProcessor;
        $this->countryRepository = $countryRepository;
        $this->lexicon = $lexicon;
        $this->deliveryMethodKey = $deliveryMethodKey;
    }

    public function handle(&$params)
    {
        $currentDelivery = $this->ordersProcessor->getCurrentDelivery();
        $selectedCountry = $this->countryRepository->getSelectedCountryFromRequest();


        if ($currentDelivery != $this->deliveryMethodKey) {
            return;
        }
        $rules = [
            'delivery_goshippo_country' => [
                'required' => $this->lexicon->get('select_country'),
            ],
            'delivery_goshippo_zip' => [
                'required' => $this->lexicon->get('fill_zip'),
            ],
            'delivery_goshippo_city' => [
                'required' => $this->lexicon->get('fill_city'),
            ],
            'delivery_goshippo_street' => [
                'required' => $this->lexicon->get('fill_street'),
            ],
            'delivery_goshippo_rate' => [
                'required' => $this->lexicon->get('select_rate'),
            ],

        ];
        if ($selectedCountry['require_state']) {
            $rules['delivery_goshippo_state'] = [
                'required' => $this->lexicon->get('select_state')
            ];
        }
        $params['config']['rules'] = array_merge($params['config']['rules'], $rules);

    }
}