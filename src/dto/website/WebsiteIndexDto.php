<?php

namespace app\dto\website;

class WebsiteIndexDto
{
    /** @var string */
    public $content;
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
