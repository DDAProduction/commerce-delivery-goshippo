<?php

namespace CommerceDeliveryGoshippo\Services\Backend;


use Commerce\Commerce;
use CommerceDeliveryGoshippo\Contacts\RequestProductsProvider;
use CommerceDeliveryGoshippo\Container;

class BackendCartProductsGetter implements RequestProductsProvider
{

    public function getProducts()
    {
        return $_REQUEST['cart'];
    }
}