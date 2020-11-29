<?php

declare(strict_types=1);

namespace App\Common\Dto\Website;

use MongoDB\BSON\ObjectId;

class WebsiteReactionDto
{
    /** @var string */
    public $reaction;

    /** @var ObjectId */
    public $websiteId;

    /** @var string */
    public $ip;

    /** @var string */
    public $userAgent;

    /** @var ObjectId|null */
    public $user_id;
}
