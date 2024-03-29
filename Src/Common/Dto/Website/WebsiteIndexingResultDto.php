<?php

namespace App\Common\Dto\Website;

use App\Common\Models\WebsiteIndexHistory;

class WebsiteIndexingResultDto
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_WEBSITE_UNAVAILABLE = 'unavailable';

    /** @var WebsiteIndexHistory */
    public $historyRow;
    /** @var string */
    public $status;
    /** @var string[][] */
    public $errors;
}
