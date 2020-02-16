<?php

namespace app\middlewares;

use app\abstracts\BaseMiddleware;
use app\services\MetricsCollector;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class WebMetricsMiddleware extends BaseMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $time = microtime(true);
        $this->getMetrics()
            ->getOrRegisterCounter(MetricsCollector::NS_WEB_APP, MetricsCollector::TICK_APP_START, '')
            ->inc();
        $response = parent::__invoke($request, $response, $next);
        $this->getMetrics()
            ->getOrRegisterHistogram(MetricsCollector::NS_WEB_APP, MetricsCollector::TIME_APP_LATENCY, '')
            ->observe(microtime(true) - $time);

        return $response;
    }
}
