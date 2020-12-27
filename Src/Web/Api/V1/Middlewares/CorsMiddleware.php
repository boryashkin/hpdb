<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Middlewares;

use App\Common\Abstracts\BaseMiddleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * For non OPTIONS requests, keep in sync with nginx config
 */
class CorsMiddleware extends BaseMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $response = $response->withAddedHeader('Access-Control-Allow-Origin', '*');
        $response = $response->withAddedHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD');
        $response = $response->withAddedHeader(
            'Access-Control-Allow-Headers',
            'Authorization, Origin, X-Requested-With, Content-Type, Accept'
        );

        $response = parent::__invoke($request, $response, $next);

        return $response;
    }
}
