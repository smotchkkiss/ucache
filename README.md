# UCache

A simple, filesystem based html(/xml/json/...) cache

## Installation

Via composer:

```sh
composer require em4nl/ucache
```

## Usage

Assuming you're using autoloading and your composer vendor dir is
at `./vendor`:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$cache = new Em4nl\U\Cache(__DIR__ . '/cache');

// not mandatory: register cache invalidation function
$cache->invalidate(function($filename) {
    // don't serve files from cache that are older than 3 hours
    // (returning TRUE here means TO INVALIDATE, returning false
    // or nothing means the file remains in the cache!)
    return filemtime($filename) < time() - 60 * 60 * 3;
});

// try to serve from cache; if that fails, create the output and
// cache it for next time
if (!$cache->serve()) {
    $cache->start();
    echo 'Hello World';
    $cache->end();
}
```

## License

[The MIT License](https://github.com/em4nl/ucache/blob/master/LICENSE)
