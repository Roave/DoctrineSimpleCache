<?php
declare(strict_types = 1);

namespace RoaveTestAsset\DoctrineSimpleCache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\MultiOperationCache;

interface NotClearableCache extends Cache, MultiOperationCache
{
}
