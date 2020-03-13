<?php

namespace App\Common\MessageBus\Handlers\Persistors;

interface PersistorInterface
{
    public const TRANSPORT = CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_PERSISTORS;
}
