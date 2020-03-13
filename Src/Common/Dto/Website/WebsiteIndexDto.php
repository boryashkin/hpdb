<?php

namespace App\Common\Dto\Website;

class WebsiteIndexDto
{
    /** @var string in utf-8 */
    public $content;
    /** @var string */
    public $initialEncoding;
    /** @var int */
    public $httpStatus;
    /** @var string[][] */
    public $httpHeaders;
    /** @var string[] */
    public $redirects;
    /** @var bool */
    public $available;
    /** @var float */
    public $time;
}
