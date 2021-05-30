<?php


namespace CommerceDeliveryGoshippo\Actions;


use CommerceDeliveryGoshippo\Container;
use Helpers\Config;
use Helpers\Lexicon;

class OnManagerBeforeOrderRenderAction
{
    /**
     * @var Lexicon
     */
    private $lexicon;
    /**
     * @var Config
     */
    private $config;

    public function __construct(Container $container)
    {
        $this->lexicon = $container->get(Lexicon::class);
        $this->config = $container->get(Config::class);
    }

    public function handle(&$params)
    {


        if (
            $params['order']['fields']['delivery_method'] === $this->config->getCFGDef('deliveryMethodKey')

        ) {


            if (!empty($params['order']['fields']['delivery_goshippo_transaction']['label_url'])) {

                $params['groups']['payment_delivery']['fields']['label_url'] = [
                    'title' => $this->lexicon->get('label_url'),
                    'content' => function ($data) {
                        return '<a target="_blank" href=' . $data['fields']['delivery_goshippo_transaction']['label_url'] . '>Follow</a>';
                    },
                    'sort' => 21,
                ];

                $params['groups']['payment_delivery']['fields']['tracking_number'] = [
                    'title' => $this->lexicon->get('tracking_number'),
                    'content' => function ($data) {


                        return $data['fields']['delivery_goshippo_transaction']['tracking_number'];
                    },
                    'sort' => 22,
                ];

                $params['groups']['payment_delivery']['fields']['tracking_url_provider'] = [
                    'title' => $this->lexicon->get('tracking_url_provider'),
                    'content' => function ($data) {
                        return '<a target="_blank" href=' . $data['fields']['delivery_goshippo_transaction']['tracking_url_provider'] . '>Follow</a>';
                    },
                    'sort' => 23,
                ];
            }




            elseif  (!empty($params['order']['fields']['delivery_goshippo_rate'])) {
                $params['groups']['payment_delivery']['fields']['goshippo_create_invoice'] = [
                    'title' => $this->lexicon->get('invoice'),
                    'content' => function ($data) {
                        $url = 'index.php?a=112&id=' . $_GET['id'].'&type=goshippo/index&order_id='.$data['id'];

                        return '<a class="btn btn-success" href="'.$url.'" >'.$this->lexicon->get('generate').'</a>';
                    },
                    'sort' => 23,
                ];
            }


        }


    }

}