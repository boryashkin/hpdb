<?php

declare(strict_types=1);

namespace App\Common\Dto\Parsers;

class WebFeedItemDto
{
    public const TYPE_RSS = 'application/rss+xml';
    public const TYPE_ATOM = 'application/rss+xml';
    public const TYPE_JSON = 'application/activity+json';

    private $type;
    private $url;

    public function __construct(string $type, string $url)
    {
        $this->type = $type;
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isRss(): bool
    {
        return $this->getType() === self::TYPE_RSS;
    }

    public function isAtom(): bool
    {
        return $this->getType() === self::TYPE_ATOM;
    }

    public function isJson(): bool
    {
        return $this->getType() === self::TYPE_JSON;
    }
}
