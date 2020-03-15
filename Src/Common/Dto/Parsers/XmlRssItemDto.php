<?php

declare(strict_types=1);

namespace App\Common\Dto\Parsers;

class XmlRssItemDto
{
    public $title;
    public $link;
    public $description;
    public $author;
    public $category;
    public $comments;
    /** @var array */
    public $enclosure;
    public $guid;
    public $pubDate;
    public $source;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
