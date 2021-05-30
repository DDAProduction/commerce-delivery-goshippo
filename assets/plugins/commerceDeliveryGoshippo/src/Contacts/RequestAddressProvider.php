<?php

namespace CommerceDeliveryGoshippo\Contacts;


use CommerceDeliveryGoshippo\Container;

interface RequestAddressProvider
{
    public function __construct(Container $container);

    public function getAddress();
}