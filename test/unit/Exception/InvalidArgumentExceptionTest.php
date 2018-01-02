<?php
declare(strict_types = 1);

namespace RoaveTest\DoctrineSimpleCache\Exception;

use Roave\DoctrineSimpleCache\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

/**
 * @covers \Roave\DoctrineSimpleCache\Exception\InvalidArgumentException
 */
final class InvalidArgumentExceptionTest extends TestCase
{
    public function testFromKeyAndInvalidTTLObject()
    {
        $invalidTTL = new \stdClass();
        $exception = InvalidArgumentException::fromKeyAndInvalidTTL('key', $invalidTTL);
        self::assertInstanceOf(InvalidArgumentException::class, $exception);
        self::assertInstanceOf(PsrInvalidArgumentException::class, $exception);

        self::assertStringMatchesFormat(
            'TTL for "%s" should be defined by an integer or a DateInterval, but stdClass is given.',
            $exception->getMessage()
        );
    }
    public function testFromInvalidKeyCharacters()
    {
        $invalidKey = uniqid('invalidKey', true);
        $exception = InvalidArgumentException::fromInvalidKeyCharacters($invalidKey);
        self::assertInstanceOf(InvalidArgumentException::class, $exception);
        self::assertInstanceOf(PsrInvalidArgumentException::class, $exception);
        self::assertSame(
            sprintf(
                'Key "%s" is in an invalid format - must not contain characters: {}()/\@:',
                $invalidKey
            ),
            $exception->getMessage()
        );
    }

    public function testFromInvalidType()
    {
        $invalidKey = random_int(100, 200);
        $exception = InvalidArgumentException::fromInvalidType($invalidKey);
        self::assertInstanceOf(InvalidArgumentException::class, $exception);
        self::assertInstanceOf(PsrInvalidArgumentException::class, $exception);
        self::assertSame(
            'Key was not a valid type. Expected string, received integer',
            $exception->getMessage()
        );
    }

    public function testFromEmptyKey()
    {
        $exception = InvalidArgumentException::fromEmptyKey();
        self::assertInstanceOf(InvalidArgumentException::class, $exception);
        self::assertInstanceOf(PsrInvalidArgumentException::class, $exception);
        self::assertSame(
            'Requested key was an empty string.',
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

    public function testFromNonIterableKeys()
    {
        $invalidKey = random_int(100, 200);
        $exception = InvalidArgumentException::fromNonIterableKeys($invalidKey);
        self::assertInstanceOf(InvalidArgumentException::class, $exception);
        self::assertInstanceOf(PsrInvalidArgumentException::class, $exception);
        self::assertSame(
            'Keys passed were not iterable (i.e. \Traversable or array), received: integer',
            $exception->getMessage()
        );
    }

    public function testFromNonIterableValues()
    {
        $invalidValue = random_int(100, 200);
        $exception = InvalidArgumentException::fromNonIterableValues($invalidValue);
        self::assertInstanceOf(InvalidArgumentException::class, $exception);
        self::assertInstanceOf(PsrInvalidArgumentException::class, $exception);
        self::assertSame(
            'Values passed were not iterable (i.e. \Traversable or array), received: integer',
            $exception->getMessage()
        );
    }
}
