<?php

namespace app\messageBus\handlers\processors;

use app\messageBus\handlers\HandlerInterface;

interface ProcessorInterface extends HandlerInterface
{
    public const TRANSPORT = CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_PROCESSORS;
}
