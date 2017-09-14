<?php
declare(strict_types = 1);

namespace RoaveTest\DoctrineSimpleCache;

use Doctrine\Common\Cache\ArrayCache;
use Roave\DoctrineSimpleCache\Exception\CacheException;
use Roave\DoctrineSimpleCache\Exception\InvalidArgumentException;
use Roave\DoctrineSimpleCache\SimpleCacheAdapter;
use RoaveTestAsset\DoctrineSimpleCache\FullyImplementedCache;
use RoaveTestAsset\DoctrineSimpleCache\NotClearableCache;
use RoaveTestAsset\DoctrineSimpleCache\NotMultiOperationCache;

/**
 * @covers \Roave\DoctrineSimpleCache\SimpleCacheAdapter
 */
final class SimpleCacheAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function invalidTTLs() : array
    {
        return [
            [''],
            [true],
            [false],
            ['abc'],
            [2.5],
            [' 1'], // can be casted to a int
            ['12foo'], // can be casted to a int
            ['025'], // can be interpreted as hex
            [new \stdClass()],
            [['array']],
        ];
    }

    public function validKeys()
    {
        return [
            ['AbC19_.'],
            ['1234567890123456789012345678901234567890123456789012345678901234'],
        ];
    }

    public function invalidKeys()
    {
        return [
            [''],
            [true],
            [false],
            [null],
            [2],
            [2.5],
            ['{str'],
            ['rand{'],
            ['rand{str'],
            ['rand}str'],
            ['rand(str'],
            ['rand)str'],
            ['rand/str'],
            ['rand\\str'],
            ['rand@str'],
            ['rand:str'],
            [new \stdClass()],
            [['array']],
        ];
    }

    public function testConstructorThrowsExceptionWhenNotMultiOperationCacheIsUsed()
    {
        /** @var NotMultiOperationCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(NotMultiOperationCache::class);

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

    public function testGetWithFalseValueStoredInCache()
    {
        $key = uniqid('key', true);

        $psrCache = new SimpleCacheAdapter(new ArrayCache());
        $psrCache->set($key, false);

        self::assertFalse($psrCache->get($key, uniqid('default', true)));
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
     * @param mixed $ttl
     * @dataProvider invalidTTLs
     */
    public function testSetWithInvalidTTL($ttl)
    {
        $key = uniqid('key', true);
        $value = uniqid('value', true);

        $psrCache = new SimpleCacheAdapter(new ArrayCache());

        $this->expectException(InvalidArgumentException::class);
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

    /**
     * @param mixed $key
     * @dataProvider invalidKeys
     */
    public function testGetMultipleThrowsExceptionWithInvalidKeys($key)
    {
        $this->expectException(InvalidArgumentException::class);

        $psrCache = new SimpleCacheAdapter(new ArrayCache());
        $psrCache->getMultiple([$key]);
    }

    /**
     * @param mixed $key
     * @dataProvider validKeys
     */
    public function testGetMultipleAcceptsTraversable($key)
    {
        $values = [
            $key => uniqid('value', true),
        ];

        /** @var FullyImplementedCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(FullyImplementedCache::class);
        $doctrineCache->expects(self::once())->method('fetchMultiple')->with(array_keys($values))->willReturn($values);

        $psrCache = new SimpleCacheAdapter($doctrineCache);
        $psrCache->getMultiple(new \ArrayObject(array_keys($values)));
    }

    public function testGetMultipleAcceptsGenerator()
    {
        $values = [
            uniqid('key0', true) => uniqid('value0', true),
            uniqid('key1', true) => uniqid('value1', true),
        ];

        $generator = function () use ($values) {
            /** @noinspection ForeachOnArrayComponentsInspection */
            foreach (array_keys($values) as $k) {
                yield $k;
            }
        };

        $psrCache = new SimpleCacheAdapter(new ArrayCache());
        $psrCache->setMultiple($values);

        self::assertSame($values, $psrCache->getMultiple($generator()));
    }

    public function testGetMultipleThrowsExceptionWhenNotArrayOrTraversable()
    {
        $this->expectException(InvalidArgumentException::class);

        $psrCache = new SimpleCacheAdapter(new ArrayCache());
        $psrCache->getMultiple(uniqid('string', true));
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
     * @param mixed $ttl
     * @dataProvider invalidTTLs
     */
    public function testSetMultipleWithInvalidTTL($ttl)
    {
        $values = [
            uniqid('key1', true) => uniqid('value1', true),
            uniqid('key2', true) => uniqid('value2', true),
        ];

        $psrCache = new SimpleCacheAdapter(new ArrayCache());

        $this->expectException(InvalidArgumentException::class);
        $psrCache->setMultiple($values, $ttl);
    }

    public function testSetMultipleThrowsExceptionWhenNotArrayOrTraversable()
    {
        $this->expectException(InvalidArgumentException::class);

        $psrCache = new SimpleCacheAdapter(new ArrayCache());
        $psrCache->setMultiple(uniqid('string', true));
    }

    public function testSetMultipleAcceptsGenerator()
    {
        $key0 = uniqid('key0', true);
        $key1 = uniqid('key1', true);
        $values = [
            $key0 => uniqid('value0', true),
            $key1 => uniqid('value1', true),
        ];

        $generator = function () use ($values) {
            foreach ($values as $k => $v) {
                yield $k => $v;
            }
        };

        $psrCache = new SimpleCacheAdapter(new ArrayCache());
        $psrCache->setMultiple($generator());

        self::assertSame($values[$key0], $psrCache->get($key0));
        self::assertSame($values[$key1], $psrCache->get($key1));
    }

    public function testDeleteMultipleReturnsTrueWhenAllDeletesSucceed()
    {
        $keys = [
            uniqid('key1', true),
            uniqid('key2', true),
        ];

        /** @var FullyImplementedCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(FullyImplementedCache::class);
        $doctrineCache->expects(self::once())->method('deleteMultiple')->with($keys)->willReturn(true);

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
        $doctrineCache->expects(self::once())->method('deleteMultiple')->with($keys)->willReturn(false);

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
