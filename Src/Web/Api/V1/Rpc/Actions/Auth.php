<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Rpc\Actions;

use App\Common\Abstracts\BaseAction;
use App\Common\Repositories\UserRepository;
use App\Common\Services\UserService;
use App\Web\Api\V1\Rpc\Requests\AuthUserRequest;
use App\Web\Api\V1\Rpc\Responses\AuthUserResponse;
use Jenssegers\Mongodb\Connection;
use OpenApi\Annotations as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\SlimException;

/**
 * @OA\Put(
 *     path="/api/v1/rpc/auth",
 *     tags={"rpc"},
 *     @OA\RequestBody(
 *         description="Auth for user",
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/AuthUserRequest")
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Pfofile is authorized",
 *         @OA\JsonContent(ref="#/components/schemas/AuthUserResponse")
 *     ),
 *     @OA\Response(
 *         response="400",
 *         description="Validation errors",
 *         @OA\JsonContent()
 *     )
 * )
 */
class Auth extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getParsedBody();
        $authRequest = new AuthUserRequest();
        $authRequest->email = $params['email'] ?? null;
        $authRequest->password = $params['password'] ?? null;

        $authRequest->email = filter_var($authRequest->email, FILTER_VALIDATE_EMAIL);
        if (!$authRequest->email) {
            $response = $response->withStatus(400, 'Bad Request');
            $response->getBody()->write('email is not valid');

            throw new SlimException($request, $response);
        }

        $userRepo = new UserRepository($this->getMongo());
        $userService = new UserService($userRepo);

        $user = $userService->getOneByEmail($authRequest->email);
        if (!$user || !$userService->isPasswordValid($authRequest->password, $user)) {
            $response = $response->withStatus(400, 'Bad Request');
            $response->getBody()->write('email or password is incorrect');

            throw new SlimException($request, $response);
        }

        $token = $userService->getNewAuthToken($user);

        $authResponse = new AuthUserResponse();
        $authResponse->token = 'Bearer ' . $token;

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($authResponse));

        return $response;
    }

    /** @return Connection */
    private function getMongo()
    {
        return $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
    }
}
