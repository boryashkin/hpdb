<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Middlewares;

use App\Common\Abstracts\BaseMiddleware;
use App\Common\Repositories\UserRepository;
use App\Common\Services\AuthService;
use App\Common\Services\LocalSessionCache;
use App\Common\Services\UserService;
use Jenssegers\Mongodb\Connection;
use MongoDB\BSON\ObjectId;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Authenticates a user if token is passed
 */
class AuthenticationMiddleware extends BaseMiddleware
{
    private const AUTH_PREFIX = 'Bearer ';

    /** @var UserService */
    private $userService;

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $authLine = $request->getHeader('Authorization');
        if (!$authLine) {
            return parent::__invoke($request, $response, $next);
        }
        if (!stripos(self::AUTH_PREFIX, $authLine[0]) === 0) {
            return parent::__invoke($request, $response, $next);
        }

        $token = substr($authLine[0], strlen(self::AUTH_PREFIX));

        $user = $this->getUserService()->getUserByBearerToken($token);
        if (!$user) {
            return parent::__invoke($request, $response, $next);
        }

        $this->getAuthService()->setCurrentUserId(new ObjectId($user->_id));

        return parent::__invoke($request, $response, $next);
    }

    private function getUserService(): UserService
    {
        if (!$this->userService) {
            $this->userService = new UserService(new UserRepository($this->getMongo()));
        }

        return $this->userService;
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
