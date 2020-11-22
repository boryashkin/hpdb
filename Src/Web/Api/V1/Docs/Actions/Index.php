<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Docs\Actions;

use App\Common\Abstracts\BaseAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write(file_get_contents(__DIR__ . '/../../../../../../docs/api.yml'));

        return $response->withAddedHeader('Content-Type', 'text/yaml; charset=utf-8');
    }

}
