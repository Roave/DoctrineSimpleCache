<?php
namespace Roave\DoctrineSimpleCache;

use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

class InvalidArgumentException extends \InvalidArgumentException implements PsrInvalidArgumentException
{
    public static function fromKeyAndInvalidTTL($key, $ttl): self
    {
        return new self(sprintf(
            'TTL for "%s" should be defined by an integer or a DateInterval object, but %s is given.',
            $key, gettype($ttl)
        ));
    }
}
