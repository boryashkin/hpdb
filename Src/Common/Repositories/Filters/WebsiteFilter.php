<?php

declare(strict_types=1);

namespace App\Common\Repositories\Filters;

use App\Common\Exceptions\RepositoryFilterException;

class WebsiteFilter
{
    public $homepageLike;
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
}
