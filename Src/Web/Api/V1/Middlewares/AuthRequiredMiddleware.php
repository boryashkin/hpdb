<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Middlewares;

use App\Common\Abstracts\BaseMiddleware;
use App\Common\Repositories\UserRepository;
use App\Common\Services\AuthService;
use App\Common\Services\LocalSessionCache;
use App\Common\Services\UserService;
use Jenssegers\Mongodb\Connection;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AuthRequiredMiddleware extends BaseMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $user = $this->getAuthService()->getCurrentUser();
        if (!$user) {
            $response = $response->withStatus(401);
            $response->getBody()->write('Unauthorized');

            return $response;
        }

        return parent::__invoke($request, $response, $next);
    }

    private function getUserService(): UserService
    {
        return new UserService(new UserRepository($this->getMongo()));
    }

    private function getAuthService(): AuthService
    {
        return new AuthService($this->getLocalStorage(), $this->getUserService());
    }

    private function getLocalStorage(): LocalSessionCache
    {
        return $this->getContainer()->get(LocalSessionCache::class);
    }

    private function getMongo(): Connection
    {
        return $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
    }
}
