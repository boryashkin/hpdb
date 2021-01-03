<?php

declare(strict_types=1);

namespace App\Common\Repositories;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class DbProviderProvider implements ServiceProviderInterface
{
    public function register(Container $c)
    {
        $c->offsetSet(UserRepository::class, static function (ContainerInterface $c) {
            return new UserRepository(
                $c->offsetGet(CONTAINER_CONFIG_MONGO)
            );
        });
        $c->offsetSet(WebsiteGroupRepository::class, static function (ContainerInterface $c) {
            return new WebsiteGroupRepository(
                $c->offsetGet(CONTAINER_CONFIG_MONGO)
            );
        });
    }

    public static function getUserRepository(ContainerInterface $c): UserRepository
    {
        return $c->offsetGet(UserRepository::class);
    }

    public static function getWebsiteGroupRepository(ContainerInterface $c): WebsiteGroupRepository
    {
        return $c->offsetGet(WebsiteGroupRepository::class);
    }
}
