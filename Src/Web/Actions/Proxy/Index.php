<?php

namespace App\Web\Actions\Proxy;

use App\Common\Abstracts\BaseAction;
use App\Common\Exceptions\InvalidUrlException;
use App\Common\Repositories\ProfileRepository;
use App\Common\Repositories\WebsiteIndexHistoryRepository;
use App\Common\ValueObjects\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use function GuzzleHttp\Psr7\stream_for;
use GuzzleHttp\RedirectMiddleware;
use MongoDB\BSON\ObjectId;
use MongoDB\Exception\InvalidArgumentException;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Proxy\Filter\RemoveEncodingFilter;
use Proxy\Proxy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\InvalidMethodException;
use Slim\Exception\NotFoundException;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $mongo = $this->getContainer()->get(CONTAINER_CONFIG_MONGO);
        $repo = new ProfileRepository($mongo);
        $profileId = $request->getAttribute('id');
        if (!\is_string($profileId)) {
            throw new NotFoundException($request, $response);
        }

        try {
            $id = new ObjectId($profileId);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundException($request, $response);
        }
        if (!$profile = $repo->getOneById($id)) {
            throw new NotFoundException($request, $response);
        }

        try {
            $parsedUrl = new Url($profile['homepage']);
        } catch (InvalidUrlException $e) {
            throw new InvalidMethodException($request, $profile['homepage']);
        }
        if (\in_array($parsedUrl->getHost(), ['hpdb.ru', 'hpdb.com'])) {
            throw new \Exception('Proxying hpdb is not allowed');
        }
        if (!$path = $request->getAttribute('path')) {
            $path = $parsedUrl->getPath() ?: '/';
        }
        //No need to proxying https
        if ($profile->isHttps()) {
            $redirectToWebsite = true;
            if ($profile->content && isset($profile->content['from_website_index_history_id'])) {
                $indexRepo = new WebsiteIndexHistoryRepository($mongo);
                $index = $indexRepo->getOne($profile->content['from_website_index_history_id']);
                if (
                    in_array('sameorigin', $index->http_headers['X-Frame-Options'], true)
                    || in_array('SAMEORIGIN', $index->http_headers['X-Frame-Options'], true)
                ) {
                    $redirectToWebsite = false;
                }
            }
            if ($redirectToWebsite) {
                $url = Url::createFromNormalized($profile->scheme, $profile->homepage);

                return $response->withAddedHeader('Location', (string)$url)->withStatus(301, 'Moved permanently');
            }
        }
        // Create a guzzle client
        $stack = HandlerStack::create();
        $guzzle = new Client([
            'handler' => $stack,
            'allow_redirects' => \array_merge(RedirectMiddleware::$defaultSettings, ['track_redirects' => true]),
            'connect_timeout' => 5,
        ]);
        $proxy = new Proxy(new GuzzleAdapter($guzzle));
        $proxy->filter(new RemoveEncodingFilter());
        $clone = clone $request;
        $clone = $clone->withUri($clone->getUri()->withPath($path));

        /**
         * Crazy workaround to cache uncachable PSR Responses with streams inside (for some cases).
         */
        /** @var RedisAdapter $redis */
        $redis = $this->getContainer()->get(CONTAINER_CONFIG_REDIS_CACHE);
        $cacheKeyRsp = 'proxy' . md5($parsedUrl->getHost() . $path) . 'rsp';
        $cacheKeyBody = 'proxy' . md5($parsedUrl->getHost() . $path) . 'body';
        if ($redis->hasItem($cacheKeyRsp) && $redis->hasItem($cacheKeyBody)) {
            return $redis->getItem($cacheKeyRsp)->get()->withBody(stream_for($redis->getItem($cacheKeyBody)->get()));
        }

        try {
            $res = $proxy->forward($clone)->to($parsedUrl->getScheme() . '://' . $parsedUrl->getHost());
        } catch (ConnectException | ClientException $e) {
            return $this->getView()->render($response, 'proxy/unable.html');
        }
        if ($res->getBody()->getSize() > 15000) {
            return $res;
        }
        $redis->get($cacheKeyBody, function (ItemInterface $item) use ($res) {
            $item->expiresAfter(3600);

            return $res->getBody()->getContents();
        });

        return $redis->get($cacheKeyRsp, function (ItemInterface $item) use ($res) {
            $item->expiresAfter(3600);

            return $res;
        });
    }
}
