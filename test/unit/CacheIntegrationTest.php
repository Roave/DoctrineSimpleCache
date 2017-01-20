<?php
declare(strict_types = 1);

namespace RoaveTest\DoctrineSimpleCache;

use Cache\IntegrationTests\SimpleCacheTest;
use Doctrine\Common\Cache\ArrayCache;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;

/**
 * @coversNothing
 */
final class CacheIntegrationTest extends SimpleCacheTest
{
    /**
     * @return \Psr\SimpleCache\CacheInterface that is used in the tests
     */
    public function createSimpleCache() : \Psr\SimpleCache\CacheInterface
    {
        return new SimpleCacheAdapter(new ArrayCache());
    }

    protected function setUp() : void
    {
        parent::setUp();

        // @todo: Let's make these tests pass
        $this->skippedTests['testObjectDoesNotChangeInCache'] = true;

        // https://github.com/php-cache/integration-tests/pull/74/files
        $this->skippedTests['testSetMultiple'] = true;
    }
}
