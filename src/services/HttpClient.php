<?php

namespace app\services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RedirectMiddleware;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    /** @var string */
    private $userAgent;
    /** @var Client todo: receive it through the constructor? */
    private $client;

    public function __construct(string $userAgent)
    {
        $this->userAgent = $userAgent;
        $stack = HandlerStack::create();
        $guzzle = new Client([
            'handler' => $stack,
            'allow_redirects' => \array_merge(RedirectMiddleware::$defaultSettings, ['track_redirects' => true]),
            'connect_timeout' => 5,
        ]);
        $this->client = $guzzle;
    }

    public function requestGet(string $url, array $additionalHeaders = []): ResponseInterface
    {
        $request = new Request('GET', $url, array_merge(['User-Agent' => $this->userAgent], $additionalHeaders));
        try {
            $res = $this->client->send($request);
        } catch (TransferException $e) {
            throw $e;
        }

        return $res;
    }
}
