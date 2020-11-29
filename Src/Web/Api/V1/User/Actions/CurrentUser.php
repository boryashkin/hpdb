<?php

declare(strict_types=1);

namespace App\Web\Api\V1\User\Actions;

use App\Common\Abstracts\BaseAction;
use App\Common\Repositories\UserRepository;
use App\Common\Services\AuthService;
use App\Common\Services\LocalSessionCache;
use App\Common\Services\UserService;
use App\Web\Api\V1\User\Responses\UserResponse;
use Jenssegers\Mongodb\Connection;
use OpenApi\Annotations as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @OA\Get(
 *     path="/api/v1/rpc/current-user",
 *     tags={"user"},
 *     security={
 *       {"apiKey": {}}
 *     },
 *     @OA\Response(
 *         response="200",
 *         description="Current user",
 *         @OA\JsonContent(ref="#/components/schemas/UserResponse")
 *     ),
 *     @OA\Response(
 *         response="401",
 *         description="Unauthorized",
 *         @OA\JsonContent()
 *     )
 * )
 */
class CurrentUser extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {

        $user = $this->getAuthService()->getCurrentUser();

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode(UserResponse::createFromUser($user)));

        return $response;
    }

    /** @return Connection */
    private function getMongo()
    {
        return $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
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
}
