<?php

declare(strict_types=1);

namespace App\Web\Api\V1\User\Actions;

use App\Common\Abstracts\BaseAction;
use App\Common\Models\User;
use App\Common\Repositories\UserRepository;
use App\Common\Services\UserService;
use App\Web\Api\V1\User\Requests\UserCreateRequest;
use App\Web\Api\V1\User\Responses\UserResponse;
use Jenssegers\Mongodb\Connection;
use OpenApi\Annotations as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\SlimException;

/**
 * @OA\Post(
 *     path="/api/v1/user",
 *     tags={"user"},
 *     @OA\RequestBody(
 *         description="User creation",
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/UserCreateRequest")
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="User created",
 *         @OA\JsonContent(ref="#/components/schemas/UserResponse")
 *     ),
 *     @OA\Response(
 *         response="400",
 *         description="Validation errors",
 *         @OA\JsonContent()
 *     )
 * )
 */
class Create extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getParsedBody();
        $userCreate = new UserCreateRequest();
        $userCreate->email = $params['email'] ?? null;
        $userCreate->password = $params['password'] ?? null;

        $this->validateRequest($userCreate, $request, $response);

        $userRepo = new UserRepository($this->getMongo());
        $userService = new UserService($userRepo);

        $user = $userService->getOneByEmail($userCreate->email);
        if ($user) {
            $response = $response->withStatus(400, 'Bad Request');
            $response->getBody()->write('email is already registered');

            throw new SlimException($request, $response);
        }

        $user = new User();
        $user->email = $userCreate->email;
        $user->password = $userCreate->password;
        if (!$userService->save($user)) {
            $response = $response->withStatus(512);
            $response->getBody()->write('Unable to save a user');

            throw new SlimException($request, $response);
        }

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode(UserResponse::createFromUser($user)));

        return $response;
    }

    /** @return Connection */
    private function getMongo()
    {
        return $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
    }

    /**
     * @param UserCreateRequest $userCreate
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @throws SlimException
     */
    private function validateRequest(
        UserCreateRequest $userCreate,
        ServerRequestInterface $request,
        ResponseInterface $response
    ): void
    {
        if (!$userCreate->email || !is_string($userCreate->email)) {
            $response = $response->withStatus(400, 'Bad Request');
            $response->getBody()->write('email is required and must be a string');

            throw new SlimException($request, $response);
        }

        if (!$userCreate->password || !is_string($userCreate->password)) {
            $response = $response->withStatus(400, 'Bad Request');
            $response->getBody()->write('password is required and must be a string');

            throw new SlimException($request, $response);
        }

        $userCreate->email = filter_var($userCreate->email, FILTER_VALIDATE_EMAIL);
        if (!$userCreate->email) {
            $response = $response->withStatus(400, 'Bad Request');
            $response->getBody()->write('email is not valid');

            throw new SlimException($request, $response);
        }
    }
}
