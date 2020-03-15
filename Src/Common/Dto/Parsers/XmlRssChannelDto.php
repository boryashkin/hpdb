<?php

declare(strict_types=1);

namespace App\Common\Dto\Parsers;

class XmlRssChannelDto
{
    public $language; //en-us
    public $copyright; //Copyright 2002, Spartanburg Herald-Journal
    public $managingEditor;
    public $webMaster;
    public $pubDate;
    public $lastBuildDate;
    public $category;
    public $generator;
    public $docs;
    public $cloud;
    public $ttl;
    /** @var array */
    public $image;
    public $textInput;
    public $skipHours;
    public $skipDays;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
