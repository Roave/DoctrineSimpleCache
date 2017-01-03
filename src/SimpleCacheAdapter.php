<?php
declare(strict_types = 1);

namespace Roave\DoctrineSimpleCache;

use Doctrine\Common\Cache\Cache as DoctrineCache;
use Doctrine\Common\Cache\ClearableCache;
use Doctrine\Common\Cache\MultiGetCache;
use Doctrine\Common\Cache\MultiPutCache;
use Psr\SimpleCache\CacheInterface as PsrCache;

final class SimpleCacheAdapter implements PsrCache
{
    /**
     * @var DoctrineCache|ClearableCache|MultiGetCache|MultiPutCache
     */
    private $doctrineCache;

    /**
     * @param DoctrineCache $doctrineCache
     * @throws \Roave\DoctrineSimpleCache\CacheException
     */
    public function __construct(DoctrineCache $doctrineCache)
    {
        $this->doctrineCache = $doctrineCache;

        if (!$this->doctrineCache instanceof ClearableCache) {
            throw CacheException::fromNonClearableCache($this->doctrineCache);
        }
        if (!$this->doctrineCache instanceof MultiGetCache) {
            throw CacheException::fromNonMultiGetCache($this->doctrineCache);
        }
        if (!$this->doctrineCache instanceof MultiPutCache) {
            throw CacheException::fromNonMultiPutCache($this->doctrineCache);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get($key, $default = null)
    {
        return $this->doctrineCache->fetch($key);
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->doctrineCache->save($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($key)
    {
        return $this->doctrineCache->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        return $this->doctrineCache->deleteAll();
    }

    /**
     * {@inheritDoc}
     */
    public function getMultiple($keys, $default = null)
    {
        return $this->doctrineCache->fetchMultiple($keys);
    }

    /**
     * {@inheritDoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        return $this->doctrineCache->saveMultiple($values, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteMultiple($keys)
    {
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
    public function has($key)
    {
        return $this->doctrineCache->contains($key);
    }
}
