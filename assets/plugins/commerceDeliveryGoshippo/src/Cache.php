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
    private $storageTime = 604800;

    public function __construct(Container $ci)
    {
        $this->fs = $ci->get(FS::class);

        $this->createCacheDirIfNotExistst();

    }

    public function has($key){
        return file_exists($this->getCacheFilePath($key));
    }

    public function set($key,$value){
        $saveResult = @file_put_contents($this->getCacheFilePath($key),json_encode($value));

        if(!$saveResult){
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

    private function createCacheDirIfNotExistst()
    {
        if(!file_exists(MODX_BASE_PATH.$this->cacheDir)){
            $this->fs->makeDir(MODX_BASE_PATH.$this->cacheDir);
        }
    }

    public function clearOldCache(){
        $time = time();
        foreach (glob(MODX_BASE_PATH.$this->cacheDir.'*') as $file){
            $fileTime = filemtime($file);
            if($time-$fileTime > $this->storageTime){
                unlink($file);
            }
        }

        return 'Cleared';
    }
}