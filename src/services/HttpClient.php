<?php

namespace app\services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RedirectMiddleware;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Proxy\Filter\RemoveEncodingFilter;
use Proxy\Proxy;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    /** @var string */
    private $userAgent;
    /** @var Client todo: receive it through the constructor? */
    private $client;

    public function __construct(string $userAgent)
    {
        $stack = HandlerStack::create();
        $guzzle = new Client([
            'handler' => $stack,
            'allow_redirects' => \array_merge(RedirectMiddleware::$defaultSettings, ['track_redirects' => true]),
            'connect_timeout' => 5,
        ]);
        $this->client = $guzzle;
    }

    public function requestGet(string $url): ResponseInterface
    {
        $proxy = new Proxy(new GuzzleAdapter($this->client));
        $proxy->filter(new RemoveEncodingFilter());
        $clone = new Request('GET', '/', ['User-Agent' => $this->userAgent]);
        try {
            $res = $proxy->forward($clone)->to($url);
        } catch (TransferException $e) {
            throw $e;
        }

        return $res;
    }
}
