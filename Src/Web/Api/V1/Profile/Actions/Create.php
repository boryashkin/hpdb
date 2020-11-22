<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Profile\Actions;

use App\Common\Abstracts\BaseAction;
use App\Common\Exceptions\InvalidUrlException;
use App\Common\MessageBus\Messages\Persistors\NewWebsiteToPersistMessage;
use App\Common\Models\Website;
use App\Common\Repositories\ProfileRepository;
use App\Common\ValueObjects\Url;
use App\Web\Api\V1\Profile\Builders\WebsiteResponseBuilder;
use App\Web\Api\V1\Profile\Requests\WebsiteCreateRequest;
use Jenssegers\Mongodb\Connection;
use OpenApi\Annotations as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\SlimException;

/**
 * @OA\Post(
 *     path="/api/v1/profile",
 *     tags={"profile"},
 *     @OA\RequestBody(
 *         description="Profile creation",
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/ProfileCreateRequest")
 *     ),
 *     @OA\Response(
 *         response="200",
 *         description="Profile created",
 *         @OA\JsonContent(ref="#/components/schemas/ProfileResponse")
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
        $profileCreate = new WebsiteCreateRequest();
        if (!isset($params['website']) || !is_string($params['website'])) {
            $response = $response->withStatus(400, 'Bad Request');
            $response->getBody()->write('website is required and must be a string');

            throw new SlimException($request, $response);
        }
        $profileCreate->website = $params['website'];

        $responseBuilder = new WebsiteResponseBuilder();

        try {
            $parsedUrl = new Url($profileCreate->website);
        } catch (InvalidUrlException $e) {
            $response = $response->withStatus(400, 'Bad Request');
            $response->getBody()->write($e->getMessage());

            throw new SlimException($request, $response);
        }
        $profile = new ProfileRepository($this->getMongo());
        if ($website = $profile->getFirstOneByUrl($parsedUrl)) {
            $response = $response->withAddedHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($responseBuilder->createOne($website)));

            return $response;
        }
        $website = new Website();
        $website->homepage = $parsedUrl->getNormalized();
        $website->scheme = $parsedUrl->getScheme();
        if (!$profile->save($website)) {
            $response = $response->withStatus(512);
            $response->getBody()->write('Unable to save a website');

            throw new SlimException($request, $response);
        }
        /** @var \Symfony\Component\Messenger\MessageBusInterface $crawlersBus */
        $bus = $this->getContainer()->get(CONTAINER_CONFIG_REDIS_STREAM_PERSISTORS);

        $message = new NewWebsiteToPersistMessage($parsedUrl, 'cli', new \DateTime());
        $bus->dispatch($message);

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode($responseBuilder->createOne($website)));

        return $response;
    }

    /** @return Connection */
    private function getMongo()
    {
        return $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
    }
}
