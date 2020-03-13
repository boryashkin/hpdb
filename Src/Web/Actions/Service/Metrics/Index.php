<?php

namespace App\Web\Actions\Service\Metrics;

use App\Common\Abstracts\BaseAction;
use Prometheus\RenderTextFormat;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $renderer = new RenderTextFormat();
        $result = $renderer->render($this->getMetrics()->getMetricFamilySamples());

        $response = $response->withAddedHeader('Content-Type', RenderTextFormat::MIME_TYPE);
        $response->getBody()->write($result);

        return $response;
    }
}
