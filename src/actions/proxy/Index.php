<?php
namespace app\actions\proxy;

use app\abstracts\BaseAction;
use app\exceptions\InvalidUrlException;
use app\modules\web\ProfileRepository;
use app\valueObjects\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RedirectMiddleware;
use MongoDB\BSON\ObjectId;
use MongoDB\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Proxy\Proxy;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Proxy\Filter\RemoveEncodingFilter;
use Slim\Exception\InvalidMethodException;
use Slim\Exception\NotFoundException;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use function GuzzleHttp\Psr7\stream_for;

class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $repo = new ProfileRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO));
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
            return $response->withAddedHeader('Location', $profile['homepage'])->withStatus(301, 'Moved permanently');
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
         * Crazy workaround to cache uncachable PSR Responses with streams inside (for some cases)
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
        } catch (ConnectException $e) {
            return $this->getView()->render($response, 'proxy/unable.html');
        }
        if ($redirects = $res->getHeaderLine('X-Guzzle-Redirect-History')) {
            $redirects = explode(', ', $redirects);
            if (stripos($redirects[0], 'https:/') === 0) {
                //if there is https, we don't have to proxy it
                return $response->withAddedHeader('Location', $redirects[0])->withStatus(301, 'Moved permanently');
            }
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
