<?php

namespace app\messageBus\handlers\crawlers;

use app\messageBus\handlers\HandlerInterface;

interface CrawlerInterface extends HandlerInterface
{
    public const TRANSPORT = CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_CRAWLERS;
}
