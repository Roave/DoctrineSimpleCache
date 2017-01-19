<?php
declare(strict_types = 1);

namespace RoaveTest\DoctrineSimpleCache;

trait TTLProviderTrait
{
    public function invalidTTLs() : array
    {
        return [
            [''],
            [true],
            [false],
            [2.5],
            ['rand:str'],
            [new \stdClass()],
            [['array']],
        ];
    }
}
