<?php

declare(strict_types=1);

namespace App\Common\Services\Parsers;

use App\Common\Dto\Parsers\XmlRssChannelDto;
use App\Common\Dto\Parsers\XmlRssItemDto;

class XmlRssParserService
{
    public function __construct()
    {
        libxml_disable_entity_loader(true);
    }

    /**
     * @param string $xmlContent
     * @return XmlRssChannelDto|null
     */
    public function extractChannel(string $xmlContent): ?XmlRssChannelDto
    {
        $xml = simplexml_load_string(trim($xmlContent), 'SimpleXMLElement', LIBXML_NOCDATA);

        $items = $xml->xpath('//channel');
        if (!$items) {
            return null;
        }
        $item = (array)$items[0];
        foreach ($item as $key => $value) {
            if (is_object($value)) {
                $item[$key] = (array)$value;
            }
        }
        $feed = new XmlRssChannelDto($item);

        return $feed;
    }

    /**
     * @param string $xmlContent
     * @return XmlRssItemDto[]
     */
    public function extractItems(string $xmlContent): array
    {
        $xml = simplexml_load_string(trim($xmlContent), 'SimpleXMLElement', LIBXML_NOCDATA);

        $items = $xml->xpath('//item');
        if (!$items) {
            return [];
        }

        $dtoItems = [];
        foreach ($items as $item) {
            $item = (array)$item;
            foreach ($item as $key => $value) {
                if (is_object($value)) {
                    $item[$key] = (array)$value;
                    if (isset($item[$key]['@attributes'])) {
                        $item[$key] = $item[$key]['@attributes'];
                    }
                }
            }

            $dtoItems[] = new XmlRssItemDto((array)$item);
        }

        return $dtoItems;
    }
}
