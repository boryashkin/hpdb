<?php

namespace App\Common\MessageBus\Handlers\Processors;

use App\Common\MessageBus\Handlers\HandlerInterface;

interface ProcessorInterface extends HandlerInterface
{
    public const TRANSPORT = CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_PROCESSORS;
}
