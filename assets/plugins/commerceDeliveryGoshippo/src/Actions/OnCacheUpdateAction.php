<?php


namespace CommerceDeliveryGoshippo\Actions;


use CommerceDeliveryGoshippo\Container;

class OnCacheUpdateAction
{
    /**
     * @var \CommerceDeliveryGoshippo\Cache|false|mixed
     */
    private $cache;

    public function __construct(Container $container)
    {
        $this->cache = $container->get(\CommerceDeliveryGoshippo\Cache::class);
    }
    public function handle(&$params){
        return $this->cache->clearOldCache();

    }
}