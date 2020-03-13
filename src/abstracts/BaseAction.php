<?php

namespace app\abstracts;

use app\interfaces\Action;
use app\services\MetricsCollector;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class BaseAction implements Action
{
    private $container;
    /** @var Twig */
    private $view;
    /** @var MetricsCollector */
    private $metrics;

    /**
     * BaseAction constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->view = $this->container->view;
        $this->metrics = $container->get(CONTAINER_CONFIG_METRICS);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return Twig
     */
    public function getView()
    {
        return $this->view;
    }

    /** @return MetricsCollector */
    public function getMetrics()
    {
        return $this->metrics;
    }
}
