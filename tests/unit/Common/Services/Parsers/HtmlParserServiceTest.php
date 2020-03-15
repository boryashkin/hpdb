<?php

namespace tests\unit\Common\Services\Parsers;

use App\Common\Services\Parsers\HtmlParserService;

class HtmlParserServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @dataProvider htmlContentToExtractRss
     */
    public function testExtractWebFeedUrls(string $htmlFile, array $expectedRss)
    {
        $parser = new HtmlParserService();
        $html = file_get_contents($htmlFile);
        $result = $parser->extractWebFeeds($html);
        $feeds = [];
        foreach ($result as $item) {
            $feeds[] = [
                'type' => $item->getType(),
                'url' => $item->getUrl(),
            ];
        }

        $this->assertEquals($expectedRss, $feeds);
    }

    public function htmlContentToExtractRss(): array
    {
        return [
            [
                'html' => __DIR__ . '/data/html/emptyFile.html',
                'expectedRss' => [],
            ],
            [
                'html' => __DIR__ . '/data/html/singleRss.html',
                'expectedRss' => [
                    [
                        'type' => 'application/rss+xml',
                        'url' => 'http://localhost.org/1/a/blog',
                    ],
                ],
            ],
            [
                'html' => __DIR__ . '/data/html/singleAtom.html',
                'expectedRss' => [
                    [
                        'type' => 'application/atom+xml',
                        'url' => 'http://localhost.org/1/a/blog',
                    ],
                ],
            ],
            [
                'html' => __DIR__ . '/data/html/singleAtom.html',
                'expectedRss' => [
                    [
                        'type' => 'application/atom+xml',
                        'url' => 'http://localhost.org/1/a/blog',
                    ],
                ],
            ],
            [
                'html' => __DIR__ . '/data/html/oneAtomAndOneRss.html',
                'expectedRss' => [
                    [
                        'type' => 'application/atom+xml',
                        'url' => '/atom.xml',
                    ],
                    [
                        'type' => 'application/rss+xml',
                        'url' => '/feed.xml',
                    ],
                ],
            ],
        ];
    }
}
