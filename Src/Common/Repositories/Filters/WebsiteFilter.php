<?php

declare(strict_types=1);

namespace App\Common\Repositories\Filters;

use App\Common\Exceptions\RepositoryFilterException;

class WebsiteFilter
{
    public const SORT_ASC = 'asc';
    public const SORT_DESC = 'desc';

    public $homepageLike;
    public $sortField = '_id';
    public $sortDirection;
    public $contentExists;
    public $contentFromWebsiteIndexHistoryIdExists;
    public $group;
    public $fromId;

    private $limit;

    public function setLimit(int $limit): void
    {
        if ($limit <= 0) {
            throw new RepositoryFilterException('limit must be > 0');
        }

        $this->limit = $limit;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function setSortDirection(string $sort): void
    {
        if (!in_array($sort, [self::SORT_DESC, self::SORT_ASC])) {
            throw new RepositoryFilterException('sort direction must be one of: asc or desc');
        }

        $this->sortDirection = $sort;
    }
}
