<?php
namespace app\actions\proxy;

use app\abstracts\BaseAction;
use app\modules\web\ProfileRepository;
use Guzzle\Common\Exception\InvalidArgumentException;
use Guzzle\Http\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RedirectMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Proxy\Proxy;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Proxy\Filter\RemoveEncodingFilter;
use Slim\Exception\InvalidMethodException;
use Slim\Exception\NotFoundException;

class Index extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $repo = new ProfileRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO));
        if (!$profile = $repo->getOne($request->getAttribute('id'))) {
            throw new NotFoundException($request, $response);
        }
        try {
            $parsedUrl = Url::factory($profile['homepage']);
        } catch (InvalidArgumentException $e) {
            throw new InvalidMethodException($request, $request->getUri());
        }
        if (!$path = $request->getAttribute('path')) {
            $path = $parsedUrl->getPath() ?: '/';
        }
        //No need to proxying https
        if (stripos($profile['homepage'], 'https:/') === 0) {
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
        try {
            $res = $proxy->forward($clone)->to($profile['homepage']);
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

        return $res;
    }
}
