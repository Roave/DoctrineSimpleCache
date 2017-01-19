<?php
declare(strict_types = 1);

namespace RoaveTest\DoctrineSimpleCache;

use Doctrine\Common\Cache\ArrayCache;
use Roave\DoctrineSimpleCache\Exception\CacheException;
use Roave\DoctrineSimpleCache\Exception\InvalidArgumentException;
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
    use TTLProviderTrait;

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

    public function testGetWithNotExistingKey()
    {
        $key = uniqid('key', true);
        $value = uniqid('value', true);

        $psrCache = new SimpleCacheAdapter(new ArrayCache());
        $psrCache->set($key, $value);

        $default = uniqid('default', true);
        self::assertSame($value, $psrCache->get($key, $default));

        $anotherKey = uniqid('key', true);
        self::assertSame($default, $psrCache->get($anotherKey, $default));
    }

    public function testSetProxiesToDoctrineSave()
    {
        $key = uniqid('key', true);
        $value = uniqid('value', true);
        $ttl = random_int(1000, 9999);

        /** @var FullyImplementedCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(FullyImplementedCache::class);
        $doctrineCache->expects(self::once())->method('save')->with($key, $value, $ttl)->willReturn(true);

        $psrCache = new SimpleCacheAdapter($doctrineCache);
        self::assertTrue($psrCache->set($key, $value, $ttl));
    }

    public function testSetWithDateIntervalTTL()
    {
        $key = uniqid('key', true);
        $value = uniqid('value', true);
        $ttl_date = \DateInterval::createFromDateString('1 day');

        $psrCache = new SimpleCacheAdapter(new ArrayCache());

        // This does not test if ttl is correctly set to 86400 sec.
        self::assertTrue($psrCache->set($key, $value, $ttl_date));
        self::assertSame($psrCache->get($key), $value);
    }

    public function testSetWithNonPositiveTTL()
    {
        $key = uniqid('key', true);
        $value = uniqid('value', true);
        $ttl = random_int(1000, 9999);

        $psrCache = new SimpleCacheAdapter(new ArrayCache());

        $psrCache->set($key, $value, $ttl);
        self::assertSame($psrCache->get($key), $value);

        $psrCache->set($key, $value, -1);
        self::assertNull($psrCache->get($key), null);
    }

    /**
     * @param $ttl
     * @dataProvider invalidTTLs
     */
    public function testSetWithInvalidTTL($ttl)
    {
        self::expectException(InvalidArgumentException::class);

        $key = uniqid('key', true);
        $value = uniqid('value', true);

        $psrCache = new SimpleCacheAdapter(new ArrayCache());
        $psrCache->set($key, $value, $ttl);
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

    public function testGetMultipleWithPartialKeys()
    {
        $values = [
            uniqid('key1', true) => uniqid('value1', true),
            uniqid('key2', true) => uniqid('value2', true),
        ];
        $keys = array_keys($values);

        $psrCache = new SimpleCacheAdapter(new ArrayCache());
        $psrCache->setMultiple($values);

        $default = uniqid('default', true);
        $invalid_key = uniqid('key3', true);
        $keys[] = $invalid_key;
        $values[$invalid_key] = $default;

        self::assertSame($values, $psrCache->getMultiple($keys, $default));
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

    public function testSetMultipleWithDateIntervalTTL()
    {
        $values = [
            uniqid('key1', true) => uniqid('value1', true),
            uniqid('key2', true) => uniqid('value2', true),
        ];
        $keys = array_keys($values);
        $ttl_date = \DateInterval::createFromDateString('1 day');

        $psrCache = new SimpleCacheAdapter(new ArrayCache());

        // This does not test if ttl is correctly set to 86400 sec.
        self::assertTrue($psrCache->setMultiple($values, $ttl_date));
        self::assertSame($values, $psrCache->getMultiple($keys));
    }

    public function testSetMultipleWithNonPositiveTTL()
    {
        $values = [
            uniqid('key1', true) => uniqid('value1', true),
            uniqid('key2', true) => uniqid('value2', true),
        ];
        $keys = array_keys($values);

        $psrCache = new SimpleCacheAdapter(new ArrayCache());
        $psrCache->setMultiple($values);

        $volatile = [$keys[0] => uniqid('value3', true)];
        $psrCache->setMultiple($volatile, -1);

        self::assertNull($psrCache->get($keys[0]));
        self::assertNotNull($psrCache->get($keys[1]));
    }

    /**
     * @param $ttl
     * @dataProvider invalidTTLs
     */
    public function testSetMultipleWithInvalidTTL($ttl)
    {
        self::expectException(InvalidArgumentException::class);

        $values = [
            uniqid('key1', true) => uniqid('value1', true),
            uniqid('key2', true) => uniqid('value2', true),
        ];

        $psrCache = new SimpleCacheAdapter(new ArrayCache());
        $psrCache->setMultiple($values, $ttl);
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
