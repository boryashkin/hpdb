<?php

namespace App\Common\MessageBus\Handlers\Crawlers;

use App\Common\MessageBus\Handlers\HandlerInterface;

interface CrawlerInterface extends HandlerInterface
{
    public const TRANSPORT = CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_CRAWLERS;
}
