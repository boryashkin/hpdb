<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Middlewares;

use App\Common\Abstracts\BaseMiddleware;
use App\Common\CommonProvider;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AuthRequiredMiddleware extends BaseMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $user = CommonProvider::getAuthService($this->getContainer())->getCurrentUser();
        if (!$user) {
            $response = $response->withStatus(401);
            $response->getBody()->write('Unauthorized');

            return $response;
        }

        return parent::__invoke($request, $response, $next);
    }
}
