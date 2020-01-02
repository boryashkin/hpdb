<?php

namespace app\actions\api\v1\profile;

use app\abstracts\BaseAction;
use app\commands\ExtractIndexedContent;
use app\dto\website\WebsiteIndexingResultDto;
use app\exceptions\InvalidUrlException;
use app\models\Website;
use app\modules\web\ProfileRepository;
use app\services\HttpClient;
use app\services\website\WebsiteFetcher;
use app\services\website\WebsiteIndexer;
use app\valueObjects\Url;
use Jenssegers\Mongodb\Connection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\SlimException;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class Create extends BaseAction
{
    private const CRAWLER_USER_AGENT = 'hdpb-web/1.0';
    private const RATE_LIMIT_KEY_NAME = 'profile-create-ratelimit';

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
        $indexer = new WebsiteIndexer(
            new WebsiteFetcher(
                new HttpClient(self::CRAWLER_USER_AGENT)
            )
        );
        $repo = new ProfileRepository($this->getMongo());
        $website = $repo->getFirstOneByUrl($parsedUrl);
        if (!$website) {
            $this->setRateLimit($request, $response);
            $maxWebsite = Website::query()->max('profile_id');
            $website = new Website();
            $website->homepage = (string)$parsedUrl;
            $website->profile_id = $maxWebsite + 1;
            $website->save();
            $resultDto = $indexer->reindex($website);
            if ($resultDto->status === WebsiteIndexingResultDto::STATUS_WEBSITE_UNAVAILABLE) {
                $response = $response->withStatus(400, 'Bad Request');
                $response->getBody()->write('Website is unavailable');

                throw new SlimException($request, $response);
            }
            if ($website->isDirty()) {
                $website->save();
            }
            if ($resultDto->historyRow) {
                $resultDto->historyRow->save();
            }
            $extractor = new ExtractIndexedContent();
            $extractor->setMongo($this->getContainer()->get(CONTAINER_CONFIG_MONGO));
            $extractor->extractAndSave($resultDto->historyRow);
            $this->clearRateLimit();
        }

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
        } else {
            $redis->get(self::RATE_LIMIT_KEY_NAME, function (ItemInterface $item) {
                $item->expiresAfter(60);
                return time();
            });

        }
    }

    private function clearRateLimit()
    {
        $this->getRedis()->deleteItem(self::RATE_LIMIT_KEY_NAME);
    }
}
