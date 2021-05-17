<?php

use PHPUnit\Framework\TestCase;

define("API", "https://jsonplaceholder.typicode.com/");

class SimpleCachedJsonRequestTest extends TestCase
{
    protected $store;
    function setUp(): void
    {
        $redis = new Redis();
        $redis->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']);
        $this->store = new SimpleRedisCache($redis);
        $this->store->clear();
        SimpleCachedJsonRequest::setCacheStore($this->store);
    }

    function testRemeberCachePolicy() {
        $url = API."todos/1";
        $key = SimpleCachedJsonRequest::itemKey($url);

        $obj = json_decode('{"userId": 1,"id": 1,"title": "delectus aut autem","completed": false}');

        $from_store = $this->store->get($key);
        $this->assertNull($from_store);

        $result = SimpleCachedJsonRequest::get(API."todos/1", null, ['cachePolice' => SimpleCachedJsonRequest::REMEMBER_CACHE]);
        $from_store = $this->store->get($key);
        $this->assertEquals($from_store, $result);
    }
    function testRenewCachePolicy() {
        $url = API."todos/1";
        $key = SimpleCachedJsonRequest::itemKey($url);

        $obj_old = json_decode('{"userId": 1,"id": 1,"title": "old delectus aut autem","completed": true}');
        $obj_new = json_decode('{"userId": 1,"id": 1,"title": "delectus aut autem","completed": false}');
        $this->store->set($key, $obj_old);

        $result = SimpleCachedJsonRequest::get(API."todos/1", null, ['cachePolice' => SimpleCachedJsonRequest::RENEW_CACHE]);
        $from_store = $this->store->get($key);
        $this->assertEquals($result, $obj_new);
        $this->assertEquals($from_store, $obj_new);
    }
    function testClearCachePolicy() {
        $url = API."todos/1";
        $key = SimpleCachedJsonRequest::itemKey($url);

        $obj = json_decode('{"userId": 1,"id": 1,"title": "delectus aut autem","completed": false}');
        $this->store->set($key, $obj);

        $result = SimpleCachedJsonRequest::get(API."todos/1", null, ['cachePolice' => SimpleCachedJsonRequest::CLEAR_CACHE]);
        $from_store = $this->store->get($key);
        $this->assertNull($from_store);
    }

    function testNoStorePolicy() {
        $url = API."todos/1";
        $key = SimpleCachedJsonRequest::itemKey($url);

        $obj_old = json_decode('{"userId": 1,"id": 1,"title": "old delectus aut autem","completed": true}');
        $obj_new = json_decode('{"userId": 1,"id": 1,"title": "delectus aut autem","completed": false}');

        $result = SimpleCachedJsonRequest::get(API."todos/1", null, ['cachePolice' => SimpleCachedJsonRequest::NO_STORE]);
        $from_store = $this->store->get($key);
        $this->assertNull($from_store);

        $this->store->set($key, $obj_old);
        $result = SimpleCachedJsonRequest::get(API."todos/1", null, ['cachePolice' => SimpleCachedJsonRequest::NO_STORE]);
        $this->assertEquals($result, $obj_old);
    }

    function testNoCachePolicy() {
        $url = API."todos/1";
        $key = SimpleCachedJsonRequest::itemKey($url);

        $from_store = $this->store->get($key);
        $this->assertNull($from_store);

        $result = SimpleCachedJsonRequest::get(API."todos/1", null, ['cachePolice' => SimpleCachedJsonRequest::NO_CACHE]);
        $from_store = $this->store->get($key);
        $this->assertNull($from_store);
    }
}
