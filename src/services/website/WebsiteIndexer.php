<?php

namespace app\services\website;

use app\dto\website\WebsiteIndexingResultDto;
use app\exceptions\InvalidUrlException;
use app\models\Website;
use app\models\WebsiteIndexHistory;
use app\valueObjects\Url;
use GuzzleHttp\Exception\TransferException;
use MongoDB\BSON\ObjectId;
use \MongoDB\Driver\Exception\{UnexpectedValueException, InvalidArgumentException};

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
     * @param Website $website
     * @return WebsiteIndexingResultDto
     * @throws UnexpectedValueException|InvalidArgumentException|InvalidUrlException
     */
    public function reindex(Website $website): WebsiteIndexingResultDto
    {
        $result = new WebsiteIndexingResultDto();
        try {
            $parsedUrl = new Url($website->homepage);
        } catch (InvalidUrlException $e) {
            //to know where and which exactly exceptions are
            throw $e;
        }
        $historyRow = new WebsiteIndexHistory();
        $historyRow->website_id = new ObjectId($website->_id);

        $isHttp = $parsedUrl->getScheme() === 'http';
        $parsed = null;
        try {
            $parsed = $this->websiteFetcher->parseWebsite($parsedUrl->setScheme(Url::SCHEME_HTTPS));
            $isHttp = false;
        } catch (TransferException | InvalidUrlException $e) {
            error_log($e->getMessage());
            $result->errors[] = [$e->getCode() => $e->getMessage()];
            unset($e);
            try {
                $parsed = $this->websiteFetcher->parseWebsite($parsedUrl->setScheme(Url::SCHEME_HTTP));
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
            $website->setAttribute('homepage', \str_replace('https://', 'http://', $website->homepage));
        } elseif (!$isHttp && !$website->isHttps()) {
            $website->homepage = \str_replace('http://', 'https://', $website->homepage);
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
