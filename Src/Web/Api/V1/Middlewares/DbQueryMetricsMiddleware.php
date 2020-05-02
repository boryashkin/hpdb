<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Middlewares;

use App\Common\Abstracts\BaseMiddleware;
use App\Common\Services\MetricsCollector;
use Illuminate\Database\Events\QueryExecuted;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DbQueryMetricsMiddleware extends BaseMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $metrics = $this->getMetrics();
        $this->getDispatcher()->addListener(
            QueryExecuted::class,
            static function (QueryExecuted $event) use ($metrics) {
                $query = substr($event->sql, 0, strpos($event->sql, '('));
                $metrics
                    ->getOrRegisterHistogram(
                        MetricsCollector::NS_WEB_API,
                        MetricsCollector::getNamespaceFromString($query),
                        ''
                    )
                    ->observe($event->time);
            }
        );
        return parent::__invoke($request, $response, $next);
    }
}
