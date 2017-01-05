<?php
declare(strict_types = 1);

namespace Roave\DoctrineSimpleCache;

use Doctrine\Common\Cache\Cache as DoctrineCache;
use Doctrine\Common\Cache\CacheProvider;
use Psr\SimpleCache\CacheInterface as PsrCache;

final class SimpleCacheAdapter implements PsrCache
{
    /**
     * @var CacheProvider
     */
    private $doctrineCache;

    /**
     * @param DoctrineCache $doctrineCache
     */
    public function __construct(DoctrineCache $doctrineCache)
    {
        $this->doctrineCache = $doctrineCache;
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
    public function set($key, $value, $ttl = null) : bool
    {
        return $this->doctrineCache->save($key, $value, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($key) : bool
    {
        return $this->doctrineCache->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function clear() : bool
    {
        return $this->doctrineCache->flushAll();
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
    public function setMultiple($values, $ttl = null) : bool
    {
        return $this->doctrineCache->saveMultiple($values, $ttl);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteMultiple($keys) : bool
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
    public function has($key) : bool
    {
        return $this->doctrineCache->contains($key);
    }
}
