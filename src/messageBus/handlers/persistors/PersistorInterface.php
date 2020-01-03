<?php

namespace app\messageBus\handlers\persistors;

interface PersistorInterface
{
    public const TRANSPORT = CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_PERSISTORS;
}
