<?php

namespace tests\unit\Common\Services\Parsers;

use App\Common\Dto\Parsers\XmlAtomEntryDto;
use App\Common\Dto\Parsers\XmlAtomFeedDto;
use App\Common\Services\Parsers\XmlAtomParserService;

class XmlAtomParserServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testExtractExample()
    {
        $parser = new XmlAtomParserService();
        $xmlRss = file_get_contents(__DIR__ . '/data/atom/example.xml');
        $feed = $parser->extractFeed($xmlRss);
        $entries = $parser->extractEntries($xmlRss);
        $item = $entries[0];
        $expectedChannel = new XmlAtomFeedDto([
            'title' => 'dive into mark',
            'subtitle' => 'A &lt;em&gt;lot&lt;/em&gt; of effort went into making this effortless',
            'updated' => '2005-07-31T12:29:29Z',
            'id' => 'tag:example.org,2003:3',
            'rights' => 'Copyright (c) 2003, Mark Pilgrim',
        ]);
        $expectedItem = new XmlAtomEntryDto([
            'title' => 'Atom draft-07 snapshot',
            'updated' => '2005-07-31T12:29:29Z',
            'published' => '2003-12-13T08:29:29-04:00',
        ]);

        //todo: доделать парсер
        $this->assertEquals($expectedChannel->title, $feed->title);
        $this->assertEquals($expectedItem->title, $item->title);
        $this->assertEquals($expectedItem->updated, $item->updated);
        $this->assertEquals($expectedItem->published, $item->published);
    }
}
