<?php

namespace App\Common\Abstracts;

use App\Common\Interfaces\Middleware;
use App\Common\Services\MetricsCollector;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BaseMiddleware implements Middleware
{
    private $container;
    private $metrics;
    private $dispatcher;

    /**
     * BaseAction constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->metrics = $container->get(CONTAINER_CONFIG_METRICS);
        $this->dispatcher = $container->get(EventDispatcherInterface::class);
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        return $next($request, $response);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return MetricsCollector
     */
    public function getMetrics()
    {
        return $this->metrics;
    }

    public function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }
}
