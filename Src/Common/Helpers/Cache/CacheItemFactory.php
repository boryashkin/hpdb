<?php

declare(strict_types=1);

namespace App\Common\Helpers\Cache;

use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\CacheItem;

class CacheItemFactory
{
    public static function createCacheItem(string $key): CacheItemInterface
    {
        $construct = \Closure::bind(
            static function ($key) {
                $item = new CacheItem();
                $item->key = $key;

                return $item;
            },
            null,
            CacheItem::class
        );

        return $construct($key);
    }
}
