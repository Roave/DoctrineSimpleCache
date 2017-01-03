<?php
declare(strict_types = 1);

namespace RoaveTest\DoctrineSimpleCache;

use Doctrine\Common\Cache\Cache as DoctrineCache;
use Psr\SimpleCache\CacheException as PsrCacheException;
use Roave\DoctrineSimpleCache\CacheException;

/**
 * @covers \Roave\DoctrineSimpleCache\CacheException
 */
final class CacheExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testFromNonClearableCache()
    {
        /** @var DoctrineCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(DoctrineCache::class);

        $exception = CacheException::fromNonClearableCache($doctrineCache);

        self::assertInstanceOf(CacheException::class, $exception);
        self::assertInstanceOf(PsrCacheException::class, $exception);

        self::assertSame(
            'The given cache was not clearable, but you tried to use a feature that requires a clearable cache.',
            $exception->getMessage()
        );
    }

    public function testFromNonMultiGetCache()
    {
        /** @var DoctrineCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(DoctrineCache::class);

        $exception = CacheException::fromNonMultiGetCache($doctrineCache);

        self::assertInstanceOf(CacheException::class, $exception);
        self::assertInstanceOf(PsrCacheException::class, $exception);

        self::assertSame(
            'The given cache cannot multi-get, but you tried to use a feature that requires a multi-get cache.',
            $exception->getMessage()
        );
    }

    public function testFromNonMultiSetCache()
    {
        /** @var DoctrineCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(DoctrineCache::class);

        $exception = CacheException::fromNonMultiSetCache($doctrineCache);

        self::assertInstanceOf(CacheException::class, $exception);
        self::assertInstanceOf(PsrCacheException::class, $exception);

        self::assertSame(
            'The given cache cannot multi-set, but you tried to use a feature that requires a multi-set cache.',
            $exception->getMessage()
        );
    }
}
