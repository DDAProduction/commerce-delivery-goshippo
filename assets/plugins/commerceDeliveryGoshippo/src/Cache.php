<?php
namespace CommerceDeliveryGoshippo;


use Helpers\FS;

class Cache
{
    /**
     * @var FS
     */
    private $fs;
    private $cacheDir = 'assets/cache/goshippo/';
    public function __construct()
    {
        $this->fs = FS::getInstance();
    }

    public function has($key){
        return file_exists($this->getCacheFilePath($key));
    }

    public function set($key,$value){
        if(!@file_put_contents($key,json_encode($value))){
            throw new \Exception('Goshippo cache set failder');
        }
    }
    public function get($key){
        if(!$this->has($key)){
            return false;
        }
        return json_decode(file_get_contents($this->getCacheFilePath($key)),true);
    }

    private function getCacheFilePath($key){
        return MODX_BASE_PATH.$this->cacheDir.$key.'.json';
    }
}