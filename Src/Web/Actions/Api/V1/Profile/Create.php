<?php

namespace App\Web\Actions\Api\V1\Profile;

use App\Common\Abstracts\BaseAction;
use App\Common\Exceptions\InvalidUrlException;
use App\Common\MessageBus\Messages\Persistors\NewWebsiteToPersistMessage;
use App\Common\Models\Website;
use App\Common\Repositories\ProfileRepository;
use App\Common\ValueObjects\Url;
use Jenssegers\Mongodb\Connection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\SlimException;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Contracts\Cache\ItemInterface;

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
            $response->getBody()->write(json_encode($website));

            return $response;
        }
        $website = new Website();
        $website->homepage = (string)$parsedUrl;
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
        $response->getBody()->write(\json_encode($website));

        return $response;
    }

    /** @return Connection */
    private function getMongo()
    {
        return $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
    }

    /** @return RedisAdapter */
    private function getRedis()
    {
        return $this->getContainer()->get(CONTAINER_CONFIG_REDIS_CACHE);
    }

    private function setRateLimit(ServerRequestInterface $request, ResponseInterface $response): void
    {
        $redis = $this->getRedis();
        if ($redis->hasItem(self::RATE_LIMIT_KEY_NAME)) {
            $response = $response->withStatus(429, 'Too Many Requests');

            throw new SlimException($request, $response);
        }
        $redis->get(self::RATE_LIMIT_KEY_NAME, function (ItemInterface $item) {
            $item->expiresAfter(60);

            return time();
        });
    }

    private function clearRateLimit()
    {
        $this->getRedis()->deleteItem(self::RATE_LIMIT_KEY_NAME);
    }
}
