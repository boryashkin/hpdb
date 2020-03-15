<?php

namespace tests\unit\Common\Services\Parsers;

use App\Common\Dto\Parsers\XmlRssChannelDto;
use App\Common\Dto\Parsers\XmlRssItemDto;
use App\Common\Services\Parsers\XmlRssParserService;

class HtmlParserServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testExtractNasa()
    {
        $parser = new XmlRssParserService();
        $xmlRss = file_get_contents(__DIR__ . '/data/rss/nasa.xml');
        $channel = $parser->extractChannel($xmlRss);
        $items = $parser->extractItems($xmlRss);
        $item = $items[0];
        $expectedChannel = new XmlRssChannelDto([
            'description' => 'A RSS news feed containing the latest NASA news articles and press releases.',
            'link' => 'http://www.nasa.gov/',
            'language' => 'en-us',
            'managingEditor' => 'jim.wilson@nasa.gov',
            'webMaster' => 'brian.dunbar@nasa.gov',
            'docs' => 'http://blogs.harvard.edu/tech/rss',
        ]);
        $expectedItem = new XmlRssItemDto([
            'title' => 'NASA Astronaut Chris Cassidy Available for Interviews Before Launch',
            'description' => 'NASA astronaut Chris Cassidy will be',
            'enclosure' => [
                'url' => 'http://www.nasa.gov/sites/default/files/styles/1x1_c...itok=pN-Ngsaa',
                'length' => '1092709',
                'type' => 'image/jpeg',
            ],
            'guid' => 'http://www.nasa.gov/press-release/nasa-astronaut',
            'pubDate' => 'Thu, 12 Mar 2020 17:53 EDT',
            'source' => 'NASA Breaking News',
        ]);

        $this->assertEquals($expectedChannel, $channel);
        $this->assertEquals($expectedItem, $item);
    }

    public function testExtractSample()
    {
        $parser = new XmlRssParserService();
        $xmlRss = file_get_contents(__DIR__ . '/data/rss/sample.xml');
        $channel = $parser->extractChannel($xmlRss);
        $items = $parser->extractItems($xmlRss);
        $item = $items[0];
        $expectedChannel = new XmlRssChannelDto([
            'title' => 'FeedForAll Sample Feed',
            'description' => 'RSS is a fascinating technology. The uses for RSS',
            'link' => 'http://www.feedforall.com/industry-solutions.htm',
            'category' => 'Computers/Software/Internet/Site Management/Content Management',
            'copyright' => 'Copyright 2004 NotePage, Inc.',
            'docs' => 'http://blogs.law.harvard.edu/tech/rss',
            'language' => 'en-us',
            'lastBuildDate' => 'Tue, 19 Oct 2004 13:39:14 -0400',
            'managingEditor' => 'marketing@feedforall.com',
            'pubDate' => 'Tue, 19 Oct 2004 13:38:55 -0400',
            'webMaster' => 'webmaster@feedforall.com',
            'generator' => 'FeedForAll Beta1 (0.0.1.8)',
            'image' => [
                'url' => 'http://www.feedforall.com/ffalogo48x48.gif',
                'title' => 'FeedForAll Sample Feed',
                'link' => 'http://www.feedforall.com/industry-solutions.htm',
                'description' => 'FeedForAll Sample Feed',
                'width' => '48',
                'height' => '48',
            ],
        ]);
        $description = <<<HTML
<b>FeedForAll </b>helps Restaurant's communicate with customers. Let your customers know the latest specials or events.<br>
                <br>
                RSS feed uses include:<br>
                <i><font color="#FF0000">Daily Specials <br>
                Entertainment <br>
                Calendar of Events </i></font>
HTML;

        $expectedItem = new XmlRssItemDto([
            'title' => 'RSS Solutions for Restaurants',
            'description' => $description,
            'link' => 'http://www.feedforall.com/restaurant.htm',
            'category' => 'Computers/Software/Internet/Site Management/Content Management',
            'comments' => 'http://www.feedforall.com/forum',
            'pubDate' => 'Tue, 19 Oct 2004 11:09:11 -0400',
        ]);

        $this->assertEquals($expectedChannel, $channel);
        $this->assertEquals($expectedItem, $item);
    }
}
