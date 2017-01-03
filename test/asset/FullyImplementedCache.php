<?php
declare(strict_types = 1);

namespace RoaveTestAsset\DoctrineSimpleCache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ClearableCache;
use Doctrine\Common\Cache\MultiGetCache;
use Doctrine\Common\Cache\MultiPutCache;

interface FullyImplementedCache extends Cache, ClearableCache, MultiGetCache, MultiPutCache
{
}
