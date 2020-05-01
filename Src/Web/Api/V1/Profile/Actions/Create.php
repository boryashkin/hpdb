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
use Jenssegers\Mongodb\Connection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\SlimException;

class Create extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getParsedBody();
        if (!isset($params['website']) || !is_string($params['website'])) {
            $response = $response->withStatus(400, 'Bad Request');
            $response->getBody()->write('website is required and must be a string');

            throw new SlimException($request, $response);
        }

        $responseBuilder = new WebsiteResponseBuilder();

        try {
            $parsedUrl = new Url($params['website']);
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
