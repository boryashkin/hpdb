<?php

declare(strict_types=1);

namespace App\Common\Dto\Parsers;

class XmlAtomEntryDto
{
    public $title;
    public $updated;
    public $published;
    public $id;
    /** @var array[] of links */
    public $link;
    public $rights;
    public $generator;
    /** @var array */
    public $author;
    /** @var array[] of contibutors */
    public $contributor;
    public $content;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
