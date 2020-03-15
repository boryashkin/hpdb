<?php

declare(strict_types=1);

namespace App\Common\Dto\Website;

class WebsiteWebFeedEmbedded
{
    /** @var string */
    public $type;
    /** @var string */
    public $url;
    /** @var \DateTimeInterface */
    public $pub_date;
}
