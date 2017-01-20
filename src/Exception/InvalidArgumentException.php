<?php
declare(strict_types = 1);

namespace Roave\DoctrineSimpleCache\Exception;

class InvalidArgumentException
    extends \InvalidArgumentException
    implements \Psr\SimpleCache\InvalidArgumentException
{
    public static function fromKeyAndInvalidTTL(string $key, $ttl) : self
    {
        return new self(sprintf(
            'TTL for "%s" should be defined by an integer or a DateInterval, but %s is given.',
            $key,
            is_object($ttl) ? get_class($ttl) : gettype($ttl)
        ));
    }
}
