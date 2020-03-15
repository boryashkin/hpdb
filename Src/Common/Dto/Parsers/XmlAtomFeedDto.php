<?php

declare(strict_types=1);

namespace App\Common\Dto\Parsers;

class XmlAtomFeedDto
{
    public $title;
    public $subtitle;
    public $updated;
    public $id;
    /** @var array[] of links */
    public $link;
    public $rights;
    public $generator;
    /** @var array */
    public $author;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
