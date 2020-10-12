<?php

if (!class_exists(\Roave\DoctrineSimpleCache\CacheException::class, false)) {
    class_alias(\Roave\DoctrineSimpleCache\Exception\CacheException::class, \Roave\DoctrineSimpleCache\CacheException::class);
}
