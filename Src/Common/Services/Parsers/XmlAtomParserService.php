<?php

declare(strict_types=1);

namespace App\Common\Services\Parsers;

use App\Common\Dto\Parsers\XmlAtomEntryDto;
use App\Common\Dto\Parsers\XmlAtomFeedDto;

//todo: доделать парсер
class XmlAtomParserService
{
    public function __construct()
    {
        libxml_disable_entity_loader(true);
    }

    /**
     * @param string $xmlContent
     * @return XmlAtomFeedDto|null
     */
    public function extractFeed(string $xmlContent): ?XmlAtomFeedDto
    {
        $xml = simplexml_load_string(trim($xmlContent), 'SimpleXMLElement', LIBXML_NOCDATA);

        $feed = (array)$xml;
        if (!$feed) {
            return null;
        }
        foreach ($feed as $key => $value) {
            if (is_object($value)) {
                $feed[$key] = (array)$value;
                if (isset($feed[$key]['@attributes'])) {
                    $feed[$key] = $feed[$key]['@attributes'];
                }
            }
        }
        $feed = new XmlAtomFeedDto($feed);

        return $feed;
    }

    /**
     * @param string $xmlContent
     * @return XmlAtomEntryDto[]
     */
    public function extractEntries(string $xmlContent): array
    {
        $xml = simplexml_load_string(trim($xmlContent), 'SimpleXMLElement', LIBXML_NOCDATA);

        $items = $xml->entry;
        if (!$items) {
            return [];
        }

        $dtoItems = [];
        foreach ($items as $item) {
            $item = (array)$item;
            foreach ($item as $key => $value) {
                if (is_object($value)) {
                    $item[$key] = (array)$value;
                }
            }

            $dtoItems[] = new XmlAtomEntryDto((array)$item);
        }

        return $dtoItems;
    }
}
