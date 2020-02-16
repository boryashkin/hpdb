<?php

namespace app\services;

use Prometheus\CollectorRegistry;

class MetricsCollector extends CollectorRegistry
{
    public const NS_WEB_APP = 'web_app';
    public const NS_WEB_API = 'web_api';
    public const NS_WEB_PROXY = 'web_proxy';
    public const NS_CLI_BUS = 'web_cli_bus';

    public const TICK_APP_START = 'app_start_tick';
    public const SUFFIX_TICK = '_tick';
    public const SUFFIX_ERROR = '_error';

    public const TIME_APP_LATENCY = 'app_latency_time';

    public static function getNamespaceFromClassName(string $className): string
    {
        return str_replace('\\', '_', $className);
    }
}