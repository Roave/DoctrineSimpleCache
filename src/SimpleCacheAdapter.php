<?php
declare(strict_types = 1);

namespace Roave\DoctrineSimpleCache;

use Doctrine\Common\Cache\Cache as DoctrineCache;
use Doctrine\Common\Cache\ClearableCache;
use Doctrine\Common\Cache\MultiGetCache;
use Doctrine\Common\Cache\MultiPutCache;
use Psr\SimpleCache\CacheInterface as PsrCache;
use Roave\DoctrineSimpleCache\Exception\InvalidArgumentException;

final class SimpleCacheAdapter implements PsrCache
{
    /**
     * @var DoctrineCache|ClearableCache|MultiGetCache|MultiPutCache
     */
    private $doctrineCache;

    /**
     * @param DoctrineCache $doctrineCache
     * @throws \Roave\DoctrineSimpleCache\Exception\CacheException
     */
    public function __construct(DoctrineCache $doctrineCache)
    {
        $this->doctrineCache = $doctrineCache;

        if (!$this->doctrineCache instanceof ClearableCache) {
            throw Exception\CacheException::fromNonClearableCache($this->doctrineCache);
        }
        if (!$this->doctrineCache instanceof MultiGetCache) {
            throw Exception\CacheException::fromNonMultiGetCache($this->doctrineCache);
        }
        if (!$this->doctrineCache instanceof MultiPutCache) {
            throw Exception\CacheException::fromNonMultiPutCache($this->doctrineCache);
        }
    }

    /**
     * @param mixed $key
     * @throws \Roave\DoctrineSimpleCache\Exception\InvalidArgumentException
     */
    private function validateKey($key) : void
    {
        if (!is_string($key)) {
            throw Exception\InvalidArgumentException::fromInvalidType($key);
        }

        if ('' === $key) {
            throw Exception\InvalidArgumentException::fromEmptyKey();
        }

        if (preg_match('/[' . preg_quote('{}()/\@:', '/') . ']/', $key)) {
            throw Exception\InvalidArgumentException::fromInvalidKeyCharacters($key);
        }
    }

    /**
     * @param mixed $keys
     * @return array
     * @throws \Roave\DoctrineSimpleCache\Exception\InvalidArgumentException
     */
    private function filterValidateMultipleKeys($keys) : array
    {
        if ($keys instanceof \Traversable) {
            $keys = iterator_to_array($keys);
        }

        if (!is_array($keys)) {
            throw Exception\InvalidArgumentException::fromNonIterableKeys($keys);
        }

        array_map([$this, 'validateKey'], $keys);

        return $keys;
    }

    /**
     * {@inheritDoc}
     */
    public function get($key, $default = null)
    {
        $this->validateKey($key);

        $value = $this->doctrineCache->fetch($key);
        if ($value === false) {
            // Doctrine cache returns `false` when cache doesn't contain, but also `false` if the value stored is
            // `false`, so check to see if the cache contains the key; if so, we probably meant to return `false`
            if ($this->doctrineCache->contains($key)) {
                return false;
            }
            return $default;
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null) : bool
    {
        $this->validateKey($key);

        if ($ttl === null) {
            return $this->doctrineCache->save($key, $value);
        }

        if ($ttl instanceof \DateInterval) {
            $ttl = $this->convertDateIntervalToInteger($ttl);
        }

        if (!is_integer($ttl)) {
            throw InvalidArgumentException::fromKeyAndInvalidTTL($key, $ttl);
        }

        if ($ttl <= 0) {
            return $this->delete($key);
        }

        return $this->doctrineCache->save($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($key) : bool
    {
        $this->validateKey($key);
        return $this->doctrineCache->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function clear() : bool
    {
        return $this->doctrineCache->deleteAll();
    }

    /**
     * @param array|\Traversable $keys
     * @param mixed $default
     * @return array
     * @throws \Roave\DoctrineSimpleCache\Exception\InvalidArgumentException
     */
    public function getMultiple($keys, $default = null)
    {
        $keys = $this->filterValidateMultipleKeys($keys);
        return array_merge(array_fill_keys($keys, $default), $this->doctrineCache->fetchMultiple($keys));
    }

    /**
     * @param array|\Traversable $values
     * @param null|int|\DateInterval $ttl
     * @return bool
     * @throws \Roave\DoctrineSimpleCache\Exception\InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null) : bool
    {
        if (!$values instanceof \Traversable && !is_array($values)) {
            throw Exception\InvalidArgumentException::fromNonIterableKeys($values);
        }

        $validatedValues = [];
        foreach ($values as $k => $v) {
            $this->validateKey($k);
            $validatedValues[$k] = $v;
        }

        if ($ttl === null) {
            return $this->doctrineCache->saveMultiple($validatedValues);
        }

        if ($ttl instanceof \DateInterval) {
            $ttl = self::convertDateIntervalToInteger($ttl);
        }

        if (!is_integer($ttl)) {
            throw InvalidArgumentException::fromKeyAndInvalidTTL(key($validatedValues), $ttl);
        }

        if ($ttl <= 0) {
            return $this->deleteMultiple(array_keys($validatedValues));
        }

        return $this->doctrineCache->saveMultiple($validatedValues, $ttl);
    }

    /**
     * @param array|\Traversable $keys
     * @return bool
     * @throws \Roave\DoctrineSimpleCache\Exception\InvalidArgumentException
     */
    public function deleteMultiple($keys) : bool
    {
        $keys = $this->filterValidateMultipleKeys($keys);

        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * {@inheritDoc}
     */
    public function has($key) : bool
    {
        $this->validateKey($key);
        return $this->doctrineCache->contains($key);
    }

    private function convertDateIntervalToInteger(\DateInterval $ttl) : int
    {
        // Timestamp has 2038 year limitation, but it's unlikely to set TTL that long.
        return (new \DateTime())
            ->setTimestamp(0)
            ->add($ttl)
            ->getTimestamp();
    }
}
