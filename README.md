# Doctrine SimpleCache adapter

[![Build Status](https://travis-ci.org/Roave/DoctrineSimpleCache.svg?branch=master)](https://travis-ci.org/Roave/DoctrineSimpleCache)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Roave/DoctrineSimpleCache/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Roave/DoctrineSimpleCache/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Roave/DoctrineSimpleCache/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Roave/DoctrineSimpleCache/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/roave/doctrine-simplecache/v/stable)](https://packagist.org/packages/roave/doctrine-simplecache)
[![License](https://poser.pugx.org/roave/doctrine-simplecache/license)](https://packagist.org/packages/roave/doctrine-simplecache)

[PSR-16 SimpleCache](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-16-simple-cache.md)
implementation that accepts a Doctrine Cache and adapts it for the PSR-16 standards.

## Installation

This will install `doctrine/cache` if not already installed.

```bash
$ composer require roave/doctrine-simplecache
```

## Usage

Create your Doctrine Cache the usual way and inject it into `SimpleCacheAdapter`, for example:

```php
<?php

namespace App;

use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use Doctrine\Common\Cache\RedisCache;

final class MyCacheFactory
{
    public function __invoke(ContainerInterface $container) : PsrCacheInterface
    {
        return new SimpleCacheAdapter(new RedisCache());
    }
}
```

## Upgrade Guide

### BC Breaks in 2.0

* Support for `MultiOperationCache` added, includes support for `deleteMultiple` in Doctrine 1.7 breaks:
  * `CacheException` static constructors `fromNonMultiGetCache` and `fromNonMultiPutCache` have been replaced with
    `fromNonMultiOperationCache`.
  * `SimpleCacheAdapter` now requires an adapter implementing `MultiOperationCache`, and no longer specifically
    requires a cache implementing `MultiGetCache` or `MultiPutCache` explicitly.
