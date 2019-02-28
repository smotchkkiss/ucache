<?php

namespace Em4nl\U;

require_once dirname(__DIR__) . '/vendor/autoload.php';

use PHPUnit\Framework\TestCase;


function get_cache_dir() {
    return sys_get_temp_dir() . '/ucache_test.' . uniqid();
}


class CacheTest extends TestCase {

    function testHasDefaultProperties() {
        $cache_dir = get_cache_dir();
        $cache = new Cache($cache_dir);
        $this->assertInstanceOf(Cache::class, $cache);
        $this->assertObjectHasAttribute('dir', $cache);
        $this->assertObjectHasAttribute('types', $cache);
        $this->assertObjectHasAttribute('invalidation_callbacks', $cache);
        $this->assertIsString($cache->dir);
        $this->assertIsArray($cache->types);
        $this->assertIsArray($cache->invalidation_callbacks);
        $this->assertEquals($cache_dir, $cache->dir);
        $this->assertEquals(3, count($cache->types));
        $this->assertEmpty($cache->invalidation_callbacks);
        $this->assertEquals('html', $cache->types[0]);
        $this->assertEquals('xml', $cache->types[1]);
        $this->assertEquals('json', $cache->types[2]);
    }

    function testCanBeInitialisedWithFewerTypes() {
        $cache = new Cache(get_cache_dir(), ['html']);
        $this->assertEquals(1, count($cache->types));
        $this->assertEquals('html', $cache->types[0]);
    }

    function testCannotBeInitialisedWithoutTypes() {
        $this->expectException(\Exception::class);
        $cache = new Cache(get_cache_dir(), []);
    }

    function testRegisterInvalidationCallbacks() {
        $cache = new Cache(get_cache_dir());
        $callback1 = function() {};
        $cache->invalidate($callback1);
        $this->assertEquals(1, count($cache->invalidation_callbacks));
        $this->assertEquals($callback1, $cache->invalidation_callbacks[0]);
        $callback2 = function() {};
        $cache->invalidate($callback2);
        $this->assertEquals(2, count($cache->invalidation_callbacks));
        $this->assertEquals($callback2, $cache->invalidation_callbacks[1]);
    }
}
