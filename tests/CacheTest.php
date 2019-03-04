<?php

namespace Em4nl\U;

require_once dirname(__DIR__) . '/vendor/autoload.php';

use PHPUnit\Framework\TestCase;


function get_cache_dir() {
    return sys_get_temp_dir() . '/ucache_test.' . uniqid();
}


$mock_headers_list = [];
function headers_list() {
    global $mock_headers_list;
    return $mock_headers_list;
}

$mock_hash_algos = \hash_algos();
function hash_algos() {
    global $mock_hash_algos;
    return $mock_hash_algos;
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
        $this->assertEquals('html', array_values($cache->types)[0]);
        $this->assertEquals('xml', array_values($cache->types)[1]);
        $this->assertEquals('json', array_values($cache->types)[2]);
    }

    function testCanBeInitialisedWithFewerTypes() {
        $cache = new Cache(get_cache_dir(), ['text/html' => 'html']);
        $this->assertEquals(1, count($cache->types));
        $this->assertEquals('html', array_values($cache->types)[0]);
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

    function testGetFilenameFromUri() {
        $cache = new Cache(get_cache_dir());
        $this->assertEquals(
            'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
            $cache->get_filename('')
        );
        $this->assertEquals(
            'wurm.6d6fd5253189ae603637bddf2f4906d01a24fe653750fbd5a96aa8c3777253da',
            $cache->get_filename('wurm')
        );
        $this->assertEquals(
            'what-goes-up.d4d2f745122fbb85eed77dd5a4c20bbdc9c5f4457a4ae8ad8c205322ad0cf841',
            $cache->get_filename('/what/goes/up')
        );
        $this->assertEquals(
            'farqu.4e21133fc479e356cc1f0c231d91f85df026ea1afc4eb8e25d7528e85ddb9115',
            $cache->get_filename('/färqü/')
        );
        $this->assertNotEquals(
            'uhowfoinawdkf',
            $cache->get_filename('')
        );
        $this->assertNotEquals(
            'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
            $cache->get_filename('wurm')
        );
    }

    function testIsValidExtension() {
        $cache = new Cache(get_cache_dir());
        $this->assertTrue($cache->is_valid_extension('html'));
        $this->assertTrue($cache->is_valid_extension('xml'));
        $this->assertTrue($cache->is_valid_extension('json'));
        $this->assertFalse($cache->is_valid_extension('xhtml'));
        $this->assertFalse($cache->is_valid_extension('wav'));
    }

    function testGetExtensionFromHeader() {
        $cache = new Cache(get_cache_dir());
        $this->assertNull($cache->get_extension_from_header());
        global $mock_headers_list;
        $mock_headers_list = [
            'X-Wurm: 9003',
            'Content-Type: application/json',
        ];
        $this->assertEquals('json', $cache->get_extension_from_header());
        $mock_headers_list = ['Content-type: text/html'];
        $this->assertEquals('html', $cache->get_extension_from_header());
        $mock_headers_list = ['X-whatever: wurm'];
        $this->assertNull($cache->get_extension_from_header());
    }

    function testGetExtensionFromUri() {
        $cache = new Cache(get_cache_dir());
        $this->assertNull($cache->get_extension_from_uri(''));
        $this->assertNull($cache->get_extension_from_uri('/wurm'));
        $this->assertEquals(
            'html',
            $cache->get_extension_from_uri('/wurm.html')
        );
        $this->assertEquals(
            'xml',
            $cache->get_extension_from_uri('/welcome/ice-age.xml')
        );
        $this->assertNull($cache->get_extension_from_uri('/hey/move.mp4'));
    }

    function testAssertSha256Available() {
        Cache::assert_sha256_available();
        global $mock_hash_algos;
        $mock_hash_algos = [];
        $this->expectException(\Exception::class);
        Cache::assert_sha256_available();
        $mock_hash_algos = ['md5', 'sha1'];
        $this->expectException(\Exception::class);
        Cache::assert_sha256_available();
    }
}
