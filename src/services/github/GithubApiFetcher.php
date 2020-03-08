<?php

namespace app\services\github;

use app\dto\website\WebsiteIndexDto;
use app\services\HttpClient;
use app\valueObjects\Url;

class GithubApiFetcher
{
    /** @var HttpClient */
    private $client;
    /** @var string */
    private $authHeader;

    public function __construct(HttpClient $client, $authHeader = '')
    {
        $this->client = $client;
        $this->authHeader = $authHeader;
    }

    public function parseApi(Url $originUrl): WebsiteIndexDto
    {
        return $this->parseWebsite($originUrl, []);
    }

    public function parseApiAsAjax(Url $originUrl): WebsiteIndexDto
    {
        return $this->parseWebsite($originUrl, ['X-Requested-With' => 'XMLHttpRequest']);
    }

    private function parseWebsite(Url $originUrl, array $headers): WebsiteIndexDto
    {
        $rsp = new WebsiteIndexDto();

        $time = microtime(true);
        $res = $this->client->requestGet(
            (string)$originUrl,
            array_merge(['Authorization' => $this->authHeader], $headers)
        );
        $rsp->time = microtime(true) - $time;
        if ($redirects = $res->getHeaderLine('X-Guzzle-Redirect-History')) {
            $redirects = explode(', ', $redirects);
            $rsp->redirects = $redirects;
        }
        $rsp->httpStatus = $res->getStatusCode();
        $rsp->content = $res->getBody() ? $res->getBody()->getContents() : null;
        $rsp->httpHeaders = $res->getHeaders();
        $rsp->initialEncoding = null;

        return $rsp;
    }
}
