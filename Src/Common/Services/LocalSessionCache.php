<?php

declare(strict_types=1);

namespace App\Common\Services;

use App\Common\Exceptions\NotImplementedException;
use App\Common\Helpers\Cache\CacheItemFactory;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

class LocalSessionCache implements CacheInterface
{
    /** @var CacheItemPoolInterface */
    private $implementation;

    public function __construct(CacheItemPoolInterface $implementation)
    {
        $this->implementation = $implementation;
    }

    public function get($key, $default = null)
    {
        $item = $this->implementation->getItem($key);
        if (!$item->isHit()) {
            return $default;
        }

        return $item->get();
    }

    public function set($key, $value, $ttl = null)
    {
        $item = CacheItemFactory::createCacheItem($key);
        $item->set($value);
        if (is_int($ttl)) {
            $item->expiresAfter(new \DateInterval("PT{$ttl}S"));
        }

        return $this->implementation->save($item);
    }

    public function delete($key): bool
    {
        return $this->implementation->deleteItem($key);
    }

    public function clear(): bool
    {
        return $this->implementation->clear();
    }

    public function getMultiple($keys, $default = null)
    {
        throw new NotImplementedException('Not implemented yet');
    }

    public function setMultiple($values, $ttl = null)
    {
        throw new NotImplementedException('Not implemented yet');
    }

    public function deleteMultiple($keys)
    {
        throw new NotImplementedException('Not implemented yet');
    }

    public function has($key): bool
    {
        return $this->implementation->hasItem($key);
    }
}
