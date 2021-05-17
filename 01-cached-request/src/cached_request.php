<?php

class SimpleCachedJsonRequest extends SimpleJsonRequest
{
    const REMEMBER_CACHE = 'REMEMBER_CACHE';
    const RENEW_CACHE = 'RENEW_CACHE';
    const CLEAR_CACHE = 'CLEAR_CACHE';
    const NO_STORE = 'NO_STORE';
    const NO_CACHE = 'NO_CACHE';

    static protected $defaultOptions = [
        'method' => 'get',
        'parameters' => null,
        'cachePolice' => self::REMEMBER_CACHE,
        'cacheExpiration' => 60
    ];
    static private $cacheStore;

    public static function getCacheStore()
    {
        return self::$cacheStore;
    }

    public static function setCacheStore($cacheStore)
    {
        self::$cacheStore = $cacheStore;
    }
    public static function getOptions()
    {
        return self::$defaultOptions;
    }

    public static function setOptions($options)
    {
        self::$defaultOptions = $options;
    }

    public static function itemKey($url, $parameters = null)
    {
        $url .= ($parameters ? '?' . http_build_query($parameters) : '');
        return md5($url);
    }

    public static function request($url, $options)
    {
        $config = array_merge(self::$defaultOptions, $options);
        $method = $parameters = $data = $minutes = $cachePolice = $cacheExpiration = null;

        extract ($config, EXTR_OVERWRITE);
        $itemKey = self::itemKey($url, $parameters);
        if (self::REMEMBER_CACHE === $cachePolice) {
            return self::cache()->remember($itemKey, function () use ($method, $url, $parameters,$data) {
                return parent::$method($url, $parameters);
            }, $cacheExpiration);
        }
        if(self::NO_STORE === $cachePolice) {
            return self::cache()->get($itemKey, function () use ($method, $url, $parameters, $data) {
                return parent::$method($url, $parameters, $data);
            });
        }

        $response = call_user_func([get_parent_class(__CLASS__), $method], $url, $parameters, $data);

        if (self::RENEW_CACHE === $cachePolice) {
            self::cache()->set($itemKey, $response, $cacheExpiration);
        }
        if(self::CLEAR_CACHE === $cachePolice) {
            self::cache()->del($itemKey);
        }
        return $response;
    }

    public static function get(string $url, array $parameters = null, $options=null)
    {
        $options['method'] = 'get';
        $options['parameters'] = $parameters;
        return self::request($url, $options);
    }


    public static function post(string $url, array $parameters = null, array $data)
    {
        $options['method'] = 'post';
        $options['parameters'] = $parameters;
        $options['data'] = $data;
        return self::request($url, $parameters);
    }

    public static function put(string $url, array $parameters = null, array $data=null, array $options=null)
    {
        $parameters['method'] = 'post';
        return self::request($url, $parameters);
    }

    public static function patch(string $url, array $parameters = null, array $data = null, array $options=null)
    {
        $parameters['method'] = 'post';
        return self::request($url, $parameters);
    }

    public static function delete(string $url, array $parameters = null, array $data = null, array $options=null)
    {
        $parameters['method'] = 'post';
        return self::request($url, $parameters);
    }

    /**
     * @return SimpleRedisCache
     */
    private static function cache()
    {
        if (!self::$cacheStore) {
            throw new Exception('SimpleCachedJsonRequest needs a cacheStore instance');
        }
        return self::$cacheStore;
    }


}