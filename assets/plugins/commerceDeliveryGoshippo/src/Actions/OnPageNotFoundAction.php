<?php


namespace CommerceDeliveryGoshippo\Actions;


use CommerceDeliveryGoshippo\Container;
use Exception;

class OnPageNotFoundAction
{
    /**
     * @var \CommerceDeliveryGoshippo\GoshippoController
     */
    private $controller;

    public function __construct(Container $container)
    {
        $this->controller = new \CommerceDeliveryGoshippo\GoshippoController($container);
    }
    public function handle($params){


        if (!preg_match('~^ajax/commerce/delivery/goshippo/(.*)$~', $_GET['q'], $matches)) {
            return true;
        }
        $action = $matches[1];



        $response = [];

        $routes = [
            'states'=>'states',
            'rates-calculate'=>'ratesCalculate',
        ];


        if(array_key_exists($action,$routes)){


            try {
                $response = call_user_func_array([$this->controller,$routes[$action]],[$_REQUEST]);
                $response['status'] = true;
            }
            catch (Exception $e){
                $response = [
                    'status'=>false,
                    'message'=>$e->getMessage()
                ];
            }
        }

        if(is_array($response)){
            header('Content-type: text/json');
            echo json_encode($response);
        }
        else{
            echo $response;
        }

        die();
    }
}