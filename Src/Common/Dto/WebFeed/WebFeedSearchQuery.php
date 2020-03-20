<?php

declare(strict_types=1);

namespace App\Common\Dto\WebFeed;

/**
 * @todo: implement scroll api
 */
class WebFeedSearchQuery
{
    public const SORT_ASC = 'asc';
    public const SORT_DESC = 'desc';

    private $from = 0;
    private $size = 10;
    private $sort;

    public function setFrom(int $from = 0): self
    {
        $this->from = $from;

        return $this;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setSize(int $size = 10): self
    {
        $this->size = $size;

        return $this;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSort(string $sort, string $direction = self::SORT_ASC): void
    {
        $this->sort = "{$sort}:{$direction}";
    }

    public function getSort(): ?string
    {
        return $this->sort;
    }
}
