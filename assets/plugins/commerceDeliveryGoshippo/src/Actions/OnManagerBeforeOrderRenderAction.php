<?php


namespace CommerceDeliveryGoshippo\Actions;


use Helpers\Lexicon;

class OnManagerBeforeOrderRenderAction
{
    /**
     * @var Lexicon
     */
    private $lexicon;

    public function __construct(Lexicon $lexicon)
    {
        $this->lexicon = $lexicon;
    }

    public function handle(&$params)
    {

        $params['groups']['payment_delivery']['fields']['label_url'] = [
            'title' => $this->lexicon->get('label_url'),
            'content' => function ($data) {
                return '<a target="_blank" href=' . $data['fields']['goshippo']['transaction']['label_url'] . '>Follow</a>';
            },
            'sort' => 21,
        ];

        $params['groups']['payment_delivery']['fields']['tracking_number'] = [
            'title' => $this->lexicon->get('tracking_number'),
            'content' => function ($data) {


                return $data['fields']['goshippo']['transaction']['tracking_number'];
            },
            'sort' => 22,
        ];

        $params['groups']['payment_delivery']['fields']['tracking_url_provider'] = [
            'title' => $this->lexicon->get('tracking_url_provider'),
            'content' => function ($data) {
                return '<a target="_blank" href=' . $data['fields']['goshippo']['transaction']['tracking_url_provider'] . '>Follow</a>';
            },
            'sort' => 23,
        ];


    }

}