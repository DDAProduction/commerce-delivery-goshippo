<?php


namespace CommerceDeliveryGoshippo\Actions;


use CommerceDeliveryGoshippo\Renderer;
use CommerceDeliveryGoshippo\Repositories\CountryRepository;
use CommerceDeliveryGoshippo\Repositories\StateRepository;
use Helpers\Lexicon;

class OnManagerBeforeOrderEditRenderAction
{
    /**
     * @var Lexicon
     */
    private $lexicon;
    /**
     * @var \DocumentParser
     */
    private $modx;
    /**
     * @var Renderer
     */
    private $renderer;
    private $showOnlyCountries;

    public function __construct(Lexicon $lexicon,\DocumentParser $modx,Renderer $renderer,$showOnlyCountries)
    {

        $this->lexicon = $lexicon;
        $this->modx = $modx;
        $this->renderer = $renderer;
        $this->showOnlyCountries = $showOnlyCountries;
    }

    public function handle(&$params){
        $modx = $this->modx;
        $render = $this->renderer;

        $langCode = $this->lexicon->get('lang_code');
        $showOnlyCountries = $this->showOnlyCountries;



        $params['fields']['delivery_goshippo_country'] = [
            'title' => $this->lexicon->get('country'),

            'content' => function ($data) use ($modx, $langCode, $showOnlyCountries, $render) {
                $countryRepository = new CountryRepository($modx, $langCode, $showOnlyCountries);
                $fields = $data['fields'];

                return $render->render('module/country.php', [
                    'countries' => $countryRepository->all(),
                    'selectedCountry' => $countryRepository->get($fields['delivery_goshippo_country']),

                ]);
            },
            'sort' => 41,
        ];

        $params['fields']['delivery_goshippo_state'] = [
            'title' => $this->lexicon->get('state'),

            'content' => function ($data) use ($modx, $langCode, $showOnlyCountries, $render) {
                $stateRepository = new StateRepository($modx, $langCode);
                $fields = $data['fields'];

                $states = [];
                $selectedState = null;

                if ($fields['delivery_goshippo_country']) {
                    $states = $stateRepository->getCountryStates($fields['delivery_goshippo_country']);
                    $selectedState = $stateRepository->getState($fields['delivery_goshippo_state'], $fields['delivery_goshippo_country']);
                }

                return $render->render('module/state.php', [
                    'states' => $states,
                    'selectedState' => $selectedState,
                ]);
            },
            'sort' => 42,
        ];
        $params['fields']['delivery_goshippo_zip'] = [
            'title' => $this->lexicon->get('zip'),

            'content' => function ($data) use ($render) {

                return $render->render('module/input.php', [
                    'name' => 'delivery_goshippo_zip',
                    'value' => $data['fields']['delivery_goshippo_zip']
                ]);
            },
            'sort' => 42,
        ];

        $params['fields']['delivery_goshippo_city'] = [
            'title' => $this->lexicon->get('city'),

            'content' => function ($data) use ($render) {
                return $render->render('module/input.php', [
                    'name' => 'delivery_goshippo_city',
                    'value' => $data['fields']['delivery_goshippo_city']
                ]);
            },
            'sort' => 42,
        ];

        $params['fields']['delivery_goshippo_street'] = [
            'title' => $this->lexicon->get('street'),

            'content' => function ($data) use ($render) {
                return $render->render('module/input.php', [
                    'name' => 'delivery_goshippo_street',
                    'value' => $data['fields']['delivery_goshippo_street']
                ]);
            },
            'sort' => 42,
        ];





        $params['fields']['delivery_goshippo_rate'] = [
            'title' => $this->lexicon->get('rate'),

            'content' => function ($data) use ($render) {

                $rates = [];

                $selectedRate = null;

                if ($data['fields']['goshippo']['rate']) {

                    $rates[] = $data['fields']['goshippo']['rate'];
                    $selectedRate = $data['fields']['goshippo']['rate'];

                }
                return $render->render('module/rate.php', [
                    'rates' => $rates,
                    'selectedRate' => $selectedRate,
                ]);
            },
            'sort' => 42,
        ];
    }
}