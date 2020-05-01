<?php

namespace App\Web\Admin\Middlewares;

use App\Common\Abstracts\BaseMiddleware;
use App\Common\Services\MetricsCollector;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AdminMetricsMiddleware extends BaseMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $time = microtime(true);
        $this->getMetrics()
            ->getOrRegisterCounter(MetricsCollector::NS_WEB_ADMIN, MetricsCollector::TICK_APP_START, '')
            ->inc();
        $response = parent::__invoke($request, $response, $next);
        $this->getMetrics()
            ->getOrRegisterHistogram(MetricsCollector::NS_WEB_ADMIN, MetricsCollector::TIME_APP_LATENCY, '')
            ->observe(microtime(true) - $time);

        return $response;
    }
}
