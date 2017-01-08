<?php
declare(strict_types = 1);

use Cache\IntegrationTests\SimpleCacheTest;
use Doctrine\Common\Cache\ArrayCache;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;

final class CacheIntegrationTest extends SimpleCacheTest
{
    /**
     * @return \Psr\SimpleCache\CacheInterface that is used in the tests
     */
    public function createSimpleCache()
    {
        $doctrineCache = new ArrayCache();

        return new SimpleCacheAdapter($doctrineCache);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->skippedTests['testSetTtl'] = true;
        $this->skippedTests['testSetExpiredTtl'] = true;
        $this->skippedTests['testSetMultipleTtl'] = true;
        $this->skippedTests['testSetMultipleExpiredTtl'] = true;
        $this->skippedTests['testSetMultipleWithGenerator'] = true;
        $this->skippedTests['testGetMultipleWithGenerator'] = true;
        $this->skippedTests['testGetInvalidKeys'] = true;
        $this->skippedTests['testGetMultipleInvalidKeys'] = true;
        $this->skippedTests['testGetMultipleNoIterable'] = true;
        $this->skippedTests['testSetInvalidKeys'] = true;
        $this->skippedTests['testSetMultipleInvalidKeys'] = true;
        $this->skippedTests['testSetMultipleNoIterable'] = true;
        $this->skippedTests['testHasInvalidKeys'] = true;
        $this->skippedTests['testDeleteInvalidKeys'] = true;
        $this->skippedTests['testDeleteMultipleInvalidKeys'] = true;
        $this->skippedTests['testDeleteMultipleNoIterable'] = true;
        $this->skippedTests['testSetInvalidTtl'] = true;
        $this->skippedTests['testSetMultipleInvalidTtl'] = true;
        $this->skippedTests['testObjectDoesNotChangeInCache'] = true;
        $this->skippedTests['testDataTypeBoolean'] = true;
    }
}
