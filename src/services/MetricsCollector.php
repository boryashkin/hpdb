<?php

namespace app\services;

use Prometheus\CollectorRegistry;

class MetricsCollector extends CollectorRegistry
{
    public const NS_WEB_APP = 'web_app';
    public const NS_WEB_API = 'web_api';
    public const NS_WEB_PROXY = 'web_proxy';

    public const TICK_APP_START = 'app_start_tick';

    public const TIME_APP_LATENCY = 'app_latency_time';
}
