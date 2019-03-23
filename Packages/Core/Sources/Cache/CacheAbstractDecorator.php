<?php

namespace Packages\Core\Sources\Cache;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Cache\Repository;
use Illuminate\Config\Repository as ConfigRepository;
use Packages\Core\Sources\Interfaces\RepositoryInterface;
use Exception;
use ExceptionEmail;

abstract class CacheAbstractDecorator implements RepositoryInterface
{
    use CachePackageRepositoriesTrait;
    
    /**
     * @var \Packages\Core\Sources\Repositories\BaseRepository
     */
    protected $repository;

    /**
     * @var Repository
     */
    protected $cache;
    
    /**
     * @var string The entity name
     */
    protected $entityName = 'default';

    /**
     * @var int caching time
     */
    protected $cacheTime;

    public function __construct()
    {
        $this->cache = app(Repository::class);
        $this->cacheTime = app(ConfigRepository::class)->get('cache.time', 60);
    }

    /**
     * Get function not exists from cache. Transfer function via repository.
     * @param type $method 
     * @param type $parameters 
     * @author TrinhLe
     */
    public function __call($method, $parameters)
    {
        if(!method_exists($this->repository,$method))
            throw new Exception("Method $method does not exist.", 1);

        return $this->getDataWithoutCache($method, array_shift(func_get_args()));
    }

    /**
     * Get value function from repository if cache expired or invalid
     * @param $function
     * @param array $args
     * @return mixed
     * @author TrinhLe
     */
    public function getDataWithoutCache($function, array $args)
    {
        return call_user_func_array([$this->repository, $function], $args);
    }

    /**
     * @param $function
     * @param $args
     * @return mixed
     * @author TrinhLe
     */
    public function getDataIfExistCache($function, array $args)
    {
        try {

            $cacheKey = md5(get_class($this) . $function . serialize(request()->input()) . serialize(func_get_args()));

            return $this->cache
            ->tags([$this->entityName, 'global'])
            ->remember($cacheKey, $this->cacheTime,
                function () use ($function, $args){
                    return $this->getDataWithoutCache($function, $args);
                }
            );
           
        } catch (Exception $ex) {
            ExceptionEmail::report($ex,"Error use cache repository: " . get_class($this));
            return $this->getDataWithoutCache($function, $args);
        }
    }

    /**
     * Clean cache and update data.
     * @param $function
     * @param $args
     * @param boolean $flushCache
     * @author TrinhLe
     * @return mixed
     */
    public function flushCacheAndUpdateData($function, $args, bool $flushCache = true)
    {
        if ($flushCache) {
            $this->cache->tags($this->entityName)->flush();
        }

        return $this->getDataWithoutCache($function, $args);
    }
}
