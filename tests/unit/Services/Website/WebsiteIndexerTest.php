<?php

namespace tests\unit\Services\Website;

use App\Common\Dto\Website\WebsiteIndexingResultDto;
use App\Common\Exceptions\InvalidUrlException;
use App\Common\Models\Website;
use App\Common\Services\Website\WebsiteFetcher;
use App\Common\Services\Website\WebsiteIndexer;
use GuzzleHttp\Exception\TransferException;
use MongoDB\BSON\ObjectId;
use Zend\Diactoros\Response;
use App\Common\Services\HttpClient;

class WebsiteIndexerTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testReindexSuccess()
    {
        $httpClient = $this->createMock(HttpClient::class);
        $response = new Response();
        $httpContent = '<html lang="en"><head><title>Hello</title></head><body>body</body></html>';
        $response->getBody()->write($httpContent);
        $response->getBody()->rewind();
        $httpClient
            ->expects($this->once())
            ->method('requestGet')
            ->willReturn($response);

        $extractor = new WebsiteFetcher($httpClient);
        $indexer = new WebsiteIndexer($extractor);
        $website = new Website();
        $website->_id = new ObjectId('5cc98b3bc58e40004f051854');
        $website->homepage = 'http://localhost.test';
        $result = $indexer->reindex($website);

        $this->assertNotEmpty($result);
        $this->assertEquals(WebsiteIndexingResultDto::STATUS_SUCCESS, $result->status);
        $this->assertEquals($website->_id, $result->historyRow->website_id);
        $this->assertEquals($httpContent, $result->historyRow->content);
        $this->assertEquals(200, $result->historyRow->http_status);
        $this->assertEquals(false, $result->historyRow->is_http_only);
    }

    public function testReindexFailHttpError()
    {
        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->method('requestGet')
            ->willThrowException(new TransferException('Not Found', 404));

        $extractor = new WebsiteFetcher($httpClient);
        $indexer = new WebsiteIndexer($extractor);
        $website = new Website();
        $website->_id = new ObjectId('5cc98b3bc58e40004f051854');
        $website->homepage = 'http://localhost.test';
        $result = $indexer->reindex($website);

        $this->assertNotEmpty($result);
        $this->assertEquals(WebsiteIndexingResultDto::STATUS_WEBSITE_UNAVAILABLE, $result->status);
        $this->assertNotEmpty($result->errors);
    }

    public function testReindexEmptyUrlError()
    {
        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->expects($this->never())->method('requestGet');
        $this->expectException(InvalidUrlException::class);

        $extractor = new WebsiteFetcher($httpClient);
        $indexer = new WebsiteIndexer($extractor);
        $website = new Website();
        $website->_id = new ObjectId('5cc98b3bc58e40004f051854');
        $website->homepage = '';
        $indexer->reindex($website);
    }
}
