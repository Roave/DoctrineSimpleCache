<?php
declare(strict_types = 1);

namespace RoaveTest\DoctrineSimpleCache\Exception;

use Doctrine\Common\Cache\Cache as DoctrineCache;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheException as PsrCacheException;
use Roave\DoctrineSimpleCache\Exception\CacheException;

/**
 * @covers \Roave\DoctrineSimpleCache\Exception\CacheException
 */
final class CacheExceptionTest extends TestCase
{
    public function testFromNonClearableCache()
    {
        /** @var DoctrineCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(DoctrineCache::class);

        $exception = CacheException::fromNonClearableCache($doctrineCache);

        self::assertInstanceOf(CacheException::class, $exception);
        self::assertInstanceOf(PsrCacheException::class, $exception);

        self::assertStringMatchesFormat(
            'The given cache %s was not clearable, but you tried to use a feature that requires a clearable cache.',
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

        self::assertStringMatchesFormat(
            'The given cache %s cannot multi-get, but you tried to use a feature that requires a multi-get cache.',
            $exception->getMessage()
        );
    }

    public function testFromNonMultiPutCache()
    {
        /** @var DoctrineCache|\PHPUnit_Framework_MockObject_MockObject $doctrineCache */
        $doctrineCache = $this->createMock(DoctrineCache::class);

        $exception = CacheException::fromNonMultiPutCache($doctrineCache);

        self::assertInstanceOf(CacheException::class, $exception);
        self::assertInstanceOf(PsrCacheException::class, $exception);

        self::assertStringMatchesFormat(
            'The given cache %s cannot multi-put, but you tried to use a feature that requires a multi-put cache.',
            $exception->getMessage()
        );
    }
}
