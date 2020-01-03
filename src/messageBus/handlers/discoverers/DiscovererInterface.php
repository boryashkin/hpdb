<?php

namespace app\messageBus\handlers\discoverers;

use app\messageBus\handlers\HandlerInterface;

interface DiscovererInterface extends HandlerInterface
{
    public const TRANSPORT = CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_DISCOVERERS;
}
