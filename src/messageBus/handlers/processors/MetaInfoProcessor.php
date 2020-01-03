<?php

namespace app\messageBus\handlers\processors;

use app\models\WebsiteIndexHistory;

/**
 * Extracting title, description and og fields
 */
class MetaInfoProcessor implements ProcessorInterface
{
    /** @var WebsiteIndexHistory */
    private $historyItem;

    public function __construct(WebsiteIndexHistory $historyItem)
    {
        $this->historyItem = $historyItem;
    }

    public function getParsedContent(): string
    {

    }
}
