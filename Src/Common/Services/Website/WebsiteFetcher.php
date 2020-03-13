<?php

namespace App\Common\Services\Website;

use App\Common\Dto\Website\WebsiteIndexDto;
use App\Common\Exceptions\WebsiteBodyIsTooBig;
use App\Common\Services\HttpClient;
use App\Common\ValueObjects\Url;
use GuzzleHttp\Exception\TransferException;

class WebsiteFetcher
{
    /** @var HttpClient */
    private $client;
    /** @var int */
    private $maxContentSize;

    public function __construct(HttpClient $client, $maxContentSize = 300000)
    {
        $this->client = $client;
        $this->maxContentSize = $maxContentSize;
    }

    public function parseWebsiteAsAjax(Url $originUrl): WebsiteIndexDto
    {
        return $this->parseWebsite($originUrl, ['X-Requested-With' => 'XMLHttpRequest']);
    }

    /**
     * @throws TransferException|WebsiteBodyIsTooBig
     */
    public function parseWebsiteInUtf8(Url $originUrl): WebsiteIndexDto
    {
        $rsp = $this->parseWebsite($originUrl, []);

        return $this->changeEncodingTo($rsp, 'UTF-8');
    }

    private function parseWebsite(Url $originUrl, array $headers): WebsiteIndexDto
    {
        $rsp = new WebsiteIndexDto();

        $time = microtime(true);
        $res = $this->client->requestGet((string)$originUrl, $headers);
        if ($res->getBody() && $res->getBody()->getSize() > $this->maxContentSize) {
            throw new WebsiteBodyIsTooBig("Body of size is {$originUrl} " . $res->getBody()->getSize());
        }
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

    private function changeEncodingTo(WebsiteIndexDto $rsp, string $encoding): WebsiteIndexDto
    {
        if (isset($rsp->httpHeaders['Content-Type'])) {
            //create a separate processor for it
            $contentType = \end($rsp->httpHeaders['Content-Type']);
            if ($contentType && preg_match('#charset=([^()<>@,;:\"/[\]?.=\s]*)#i', $contentType, $match)) {
                $rsp->initialEncoding = trim($match[1], '"\'');
                if (strcasecmp($encoding, $rsp->initialEncoding) !== 0) {
                    $rsp->content = \iconv($rsp->initialEncoding, $encoding, $rsp->content);
                }
            }
        }

        return $rsp;
    }
}
