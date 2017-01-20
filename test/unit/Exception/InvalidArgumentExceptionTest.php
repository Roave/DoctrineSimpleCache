<?php
declare(strict_types = 1);

namespace RoaveTest\DoctrineSimpleCache\Exception;

use Roave\DoctrineSimpleCache\Exception\InvalidArgumentException;

/**
 * @covers \Roave\DoctrineSimpleCache\Exception\InvalidArgumentException
 */
final class InvalidArgumentExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testFromKeyAndInvalidTTLObject()
    {
        $invalidTTL = new \stdClass();
        $exception = InvalidArgumentException::fromKeyAndInvalidTTL('key', $invalidTTL);
        self::assertInstanceOf(InvalidArgumentException::class, $exception);
        self::assertInstanceOf(\Psr\SimpleCache\InvalidArgumentException::class, $exception);

        self::assertStringMatchesFormat(
            'TTL for "%s" should be defined by an integer or a DateInterval, but stdClass is given.',
            $exception->getMessage()
        );
    }

    public function testFromKeyAndInvalidTTLNonObject()
    {
        $invalidTTL = random_int(100, 200);
        $exception = InvalidArgumentException::fromKeyAndInvalidTTL('key', $invalidTTL);
        self::assertInstanceOf(InvalidArgumentException::class, $exception);
        self::assertInstanceOf(\Psr\SimpleCache\InvalidArgumentException::class, $exception);

        self::assertStringMatchesFormat(
            'TTL for "%s" should be defined by an integer or a DateInterval, but integer is given.',
            $exception->getMessage()
        );
    }
}
