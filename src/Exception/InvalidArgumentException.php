<?php
declare(strict_types = 1);

namespace Roave\DoctrineSimpleCache\Exception;

class InvalidArgumentException
    extends \InvalidArgumentException
    implements \Psr\SimpleCache\InvalidArgumentException
{
    public static function fromKeyAndInvalidTTL($key, $ttl) : self
    {
        return new self(sprintf(
            'TTL for "%s" should be defined by an integer or a DateInterval object, but %s is given.',
            $key,
            gettype($ttl)
        ));
    }
}
