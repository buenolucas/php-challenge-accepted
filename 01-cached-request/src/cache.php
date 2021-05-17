<?php

class SimpleRedisCache
{
    protected $redis;
    protected $default = 60;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Get a item from the cache or run a closure if is null.
     * @param $key
     * @param null $default
     * @return int|mixed|string
     */
    public function get($key, $default = null)
    {
        $value = $this->redis->get($key);
        if($value===false) {
            $value = $default instanceof Closure ? $default() : $default;

        } else {
            $value = $this->unserialize($value);

        }
        return $value;
    }

    /**
     * store an item in the cache
     * @param $key
     * @param $value
     * @param null $minutes
     */
    public function set($key, $value, $minutes=null)
    {
        $value = $this->serialize($value);
        if(is_null($minutes)) {
            $minutes = $this->getDefaultCacheTime();
        }
        $this->redis->setex($key,$minutes*60, $value);
    }

    /**
     * try get an item from the cache and store it if not exists
     * @param $key
     * @param Closure $callback
     * @param null $minutes
     * @return mixed
     */
    public function remember($key, Closure $callback,  $minutes=null) {
        $value = $this->get($key);

        if(! is_null($value) ) {
            return $value;
        }
        $value = $callback();
        $this->set($key, $value, $minutes);

        return $value;
    }

    /**
     * delete item from the cache
     * @param $key
     * @return bool
     */
    public function del($key) {
        return (bool) $this->redis->del($key);
    }

    /**
     * remove all items from cache
     * @param $key
     * @return bool
     */
    public function clear():bool {
        $this->redis->flushDB();
        return true;
    }

    /**
     * get default cache duration minutes
     * @return int
     */
    public function getDefaultCacheTime():int
    {
        return $this->default;
    }

    /**
     *  Set the default cache time in minutes.
     *
     * @param int $minutes
     * @return $this
     */
    public function setDefaultCacheTime($minutes)
    {
        $this->default = $minutes;

        return $this;
    }

    /**
     * Serialize the value.
     *
     * @param $value
     * @return mixed
     */
    protected function serialize($value)
    {
        return is_numeric($value) ? $value : serialize($value);
    }
    /**
     * Unserialize the value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        return is_numeric($value) ? $value : unserialize($value);
    }
}
