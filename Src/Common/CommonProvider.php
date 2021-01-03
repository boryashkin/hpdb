<?php

declare(strict_types=1);

namespace App\Common;

use App\Common\Repositories\DbProviderProvider;
use App\Common\Services\AuthService;
use App\Common\Services\LocalSessionCache;
use App\Common\Services\UserService;
use App\Common\Services\Website\WebsiteGroupService;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class CommonProvider implements ServiceProviderInterface
{
    public function register(Container $c)
    {
        $c->offsetSet(LocalSessionCache::class, static function (ContainerInterface $c) {
            return new LocalSessionCache(new \Symfony\Component\Cache\Adapter\ArrayAdapter());
        });

        $c->offsetSet(UserService::class, static function (ContainerInterface $c) {
            return new UserService(
                DbProviderProvider::getUserRepository($c)
            );
        });

        $c->offsetSet(UserService::class, static function (ContainerInterface $c) {
            return new UserService(
                DbProviderProvider::getUserRepository($c)
            );
        });

        $c->offsetSet(WebsiteGroupService::class, static function (ContainerInterface $c) {
            return new WebsiteGroupService(
                DbProviderProvider::getWebsiteGroupRepository($c),
                self::getCache($c)

            );
        });

        $c->offsetSet(AuthService::class, static function (ContainerInterface $c) {
            return new AuthService(
                self::getLocalSessionCache($c),
                self::getUserService($c)
            );
        });
    }

    public static function getAuthService(ContainerInterface $c): AuthService
    {
        return $c->offsetGet(AuthService::class);
    }

    public static function getLocalSessionCache(ContainerInterface $c): LocalSessionCache
    {
        return $c->offsetGet(LocalSessionCache::class);
    }

    public static function getUserService(ContainerInterface $c): UserService
    {
        return $c->offsetGet(UserService::class);
    }

    public static function getWebsiteGroupService(ContainerInterface $c): WebsiteGroupService
    {
        return $c->offsetGet(WebsiteGroupService::class);
    }

    public static function getCache(ContainerInterface $c): CacheInterface
    {
        return $c->offsetGet(CONTAINER_CONFIG_REDIS_CACHE);
    }
}
