<?php

namespace App\Common\Services\Website;

use App\Common\Dto\Website\WebsiteIndexingResultDto;
use App\Common\Exceptions\InvalidUrlException;
use App\Common\Exceptions\WebsiteBodyIsTooBig;
use App\Common\Models\Website;
use App\Common\Models\WebsiteIndexHistory;
use App\Common\ValueObjects\Url;
use GuzzleHttp\Exception\TransferException;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\UnexpectedValueException;

class WebsiteIndexer
{
    /** @var WebsiteFetcher */
    private $websiteFetcher;

    public function __construct(WebsiteFetcher $websiteFetcher)
    {
        $this->websiteFetcher = $websiteFetcher;
    }

    /**
     * Check availability http & https and set "check date".
     *
     * @throws InvalidArgumentException|InvalidUrlException|UnexpectedValueException
     */
    public function reindex(Website $website): WebsiteIndexingResultDto
    {
        $result = new WebsiteIndexingResultDto();

        try {
            $parsedUrl = Url::createFromNormalized($website->scheme, $website->homepage);
        } catch (InvalidUrlException $e) {
            //to know where and which exactly exceptions are
            throw $e;
        }
        $historyRow = new WebsiteIndexHistory();
        $historyRow->website_id = new ObjectId($website->_id);

        $isHttp = $parsedUrl->getScheme() === 'http';
        $parsed = null;

        try {
            $parsed = $this->websiteFetcher->parseWebsiteInUtf8($parsedUrl->setScheme(Url::SCHEME_HTTPS));
            $isHttp = false;
        } catch (TransferException | InvalidUrlException | WebsiteBodyIsTooBig $e) {
            error_log($e->getMessage());
            $result->errors[] = [$e->getCode() => $e->getMessage()];
            unset($e);

            try {
                $parsed = $this->websiteFetcher->parseWebsiteInUtf8($parsedUrl->setScheme(Url::SCHEME_HTTP));
                $isHttp = true;
            } catch (TransferException | InvalidUrlException $e) {
                error_log($e->getMessage());
                $result->errors[] = [$e->getCode() => $e->getMessage()];
                unset($e);
            }
        }
        if (!$parsed) {
            $historyRow->available = false;
            $result->status = WebsiteIndexingResultDto::STATUS_WEBSITE_UNAVAILABLE;

            return $result;
        }
        $historyRow->available = true;
        if ($isHttp && $website->isHttps()) {
            $website->scheme = 'http';
        } elseif (!$isHttp && !$website->isHttps()) {
            $website->scheme = 'https';
        }
        $historyRow->http_status = $parsed->httpStatus;
        $historyRow->http_headers = $parsed->httpHeaders;
        $historyRow->content = $parsed->content;
        $historyRow->redirects = $parsed->redirects;
        $historyRow->time = $parsed->time;
        $result->historyRow = $historyRow;
        $result->status = WebsiteIndexingResultDto::STATUS_SUCCESS;

        return $result;
    }
}
