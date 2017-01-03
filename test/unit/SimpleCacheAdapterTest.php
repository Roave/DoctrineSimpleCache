<?php
declare(strict_types = 1);

namespace RoaveTest\DoctrineSimpleCache;

use Roave\DoctrineSimpleCache\CacheException;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use RoaveTestAsset\DoctrineSimpleCache\FullyImplementedCache;
use RoaveTestAsset\DoctrineSimpleCache\NotClearableCache;
use RoaveTestAsset\DoctrineSimpleCache\NotMultiGettableCache;
use RoaveTestAsset\DoctrineSimpleCache\NotMultiPuttableCache;

/**
 * @covers \Roave\DoctrineSimpleCache\SimpleCacheAdapter
 */
final class SimpleCacheAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorThrowsExceptionWhenNotMultiPuttableCacheIsUsed()
    {
        /** @var NotMultiPuttableCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(NotMultiPuttableCache::class);

        $this->expectException(CacheException::class);
        new SimpleCacheAdapter($doctrineCache);
    }

    public function testConstructorThrowsExceptionWhenNotClearableCacheIsUsed()
    {
        /** @var NotClearableCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(NotClearableCache::class);

        $this->expectException(CacheException::class);
        new SimpleCacheAdapter($doctrineCache);
    }

    public function testConstructorThrowsExceptionWhenNotMultiGettableCacheIsUsed()
    {
        /** @var NotMultiGettableCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(NotMultiGettableCache::class);

        $this->expectException(CacheException::class);
        new SimpleCacheAdapter($doctrineCache);
    }

    public function testGetProxiesToDoctrineFetch()
    {
        $key = uniqid('key', true);
        $value = uniqid('value', true);

        /** @var FullyImplementedCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(FullyImplementedCache::class);
        $doctrineCache->expects(self::once())->method('fetch')->with($key)->willReturn($value);

        $psrCache = new SimpleCacheAdapter($doctrineCache);
        self::assertSame($value, $psrCache->get($key));
    }

    public function testSetProxiesToDoctrineSave()
    {
        $key = uniqid('key', true);
        $value = uniqid('value', true);
        $ttl = random_int(1000,9999);

        /** @var FullyImplementedCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(FullyImplementedCache::class);
        $doctrineCache->expects(self::once())->method('save')->with($key, $value, $ttl)->willReturn(true);

        $psrCache = new SimpleCacheAdapter($doctrineCache);
        self::assertTrue($psrCache->set($key, $value, $ttl));
    }

    public function testDeleteProxiesToDoctrineDelete()
    {
        $key = uniqid('key', true);

        /** @var FullyImplementedCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(FullyImplementedCache::class);
        $doctrineCache->expects(self::once())->method('delete')->with($key)->willReturn(true);

        $psrCache = new SimpleCacheAdapter($doctrineCache);
        self::assertTrue($psrCache->delete($key));
    }

    public function testClearProxiesToDeleteAll()
    {
        /** @var FullyImplementedCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(FullyImplementedCache::class);
        $doctrineCache->expects(self::once())->method('deleteAll')->with()->willReturn(true);

        $psrCache = new SimpleCacheAdapter($doctrineCache);
        self::assertTrue($psrCache->clear());
    }

    public function testGetMultipleProxiesToFetchMultiple()
    {
        $values = [
            uniqid('key1', true) => uniqid('value1', true),
            uniqid('key2', true) => uniqid('value2', true),
        ];
        $keys = array_keys($values);

        /** @var FullyImplementedCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(FullyImplementedCache::class);
        $doctrineCache->expects(self::once())->method('fetchMultiple')->with($keys)->willReturn($values);

        $psrCache = new SimpleCacheAdapter($doctrineCache);
        self::assertSame($values, $psrCache->getMultiple($keys));
    }

    public function testSetMultipleProxiesToSaveMultiple()
    {
        $values = [
            uniqid('key1', true) => uniqid('value1', true),
            uniqid('key2', true) => uniqid('value2', true),
        ];

        /** @var FullyImplementedCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(FullyImplementedCache::class);
        $doctrineCache->expects(self::once())->method('saveMultiple')->with($values)->willReturn(true);

        $psrCache = new SimpleCacheAdapter($doctrineCache);
        self::assertTrue($psrCache->setMultiple($values));
    }

    public function testDeleteMultipleReturnsTrueWhenAllDeletesSucceed()
    {
        $keys = [
            uniqid('key1', true),
            uniqid('key2', true),
        ];

        /** @var FullyImplementedCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(FullyImplementedCache::class);
        $doctrineCache->expects(self::at(0))->method('delete')->with($keys[0])->willReturn(true);
        $doctrineCache->expects(self::at(1))->method('delete')->with($keys[1])->willReturn(true);

        $psrCache = new SimpleCacheAdapter($doctrineCache);
        self::assertTrue($psrCache->deleteMultiple($keys));
    }

    public function testDeleteMultipleReturnsFalseWhenOneDeleteFails()
    {
        $keys = [
            uniqid('key1', true),
            uniqid('key2', true),
        ];

        /** @var FullyImplementedCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(FullyImplementedCache::class);
        $doctrineCache->expects(self::at(0))->method('delete')->with($keys[0])->willReturn(false);
        $doctrineCache->expects(self::at(1))->method('delete')->with($keys[1])->willReturn(true);

        $psrCache = new SimpleCacheAdapter($doctrineCache);
        self::assertFalse($psrCache->deleteMultiple($keys));
    }

    public function testHasProxiesToDoctrineContains()
    {
        $key = uniqid('key', true);

        /** @var FullyImplementedCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(FullyImplementedCache::class);
        $doctrineCache->expects(self::once())->method('contains')->with($key)->willReturn(true);

        $psrCache = new SimpleCacheAdapter($doctrineCache);
        self::assertTrue($psrCache->has($key));
    }
}
