<?php
namespace Roave\DoctrineSimpleCache;

use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

class InvalidArgumentException extends \InvalidArgumentException implements PsrInvalidArgumentException
{
}
