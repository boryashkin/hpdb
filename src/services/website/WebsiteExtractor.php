<?php

namespace app\services\website;

use app\dto\website\WebsiteIndexDto;
use app\services\HttpClient;
use Guzzle\Http\Url;
use GuzzleHttp\Exception\TransferException;

class WebsiteExtractor
{
    /** @var HttpClient */
    private $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param Url $originUrl
     * @return WebsiteIndexDto
     * @throws TransferException
     */
    public function parseWebsite(Url $originUrl)
    {
        $rsp = new WebsiteIndexDto();

        $time = microtime(true);
        $res = $this->client->requestGet($originUrl);
        $rsp->time = microtime(true) - $time;
        if ($redirects = $res->getHeaderLine('X-Guzzle-Redirect-History')) {
            $redirects = explode(', ', $redirects);
            $rsp->redirects = $redirects;
        }
        $rsp->httpStatus = $res->getStatusCode();
        $rsp->content = $res->getBody() ? $res->getBody()->getContents() : null;
        $rsp->httpHeaders = $res->getHeaders();

        return $rsp;
    }
}
