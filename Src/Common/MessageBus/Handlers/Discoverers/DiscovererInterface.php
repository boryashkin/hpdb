<?php

namespace App\Common\MessageBus\Handlers\Discoverers;

use App\Common\MessageBus\Handlers\HandlerInterface;

interface DiscovererInterface extends HandlerInterface
{
    public const TRANSPORT = CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_DISCOVERERS;
}
