<?php

namespace CommerceDeliveryGoshippo\Services\Front;


use Commerce\Commerce;
use CommerceDeliveryGoshippo\Contacts\RequestProductsProvider;
use CommerceDeliveryGoshippo\Container;

class FrontCartProductsGetter implements RequestProductsProvider
{

    public function getProducts()
    {

        $cart = ci()->carts->getCart('products');
        $cartItems = $cart->getItems();

        $products = [];

        foreach ($cartItems as $cartItem) {
            $products[] = [
                'id' => $cartItem['id'],
                'count' => $cartItem['count'],
            ];
        }

        return $products;
    }
}