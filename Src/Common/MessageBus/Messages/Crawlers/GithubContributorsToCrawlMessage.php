<?php

declare(strict_types=1);

namespace App\Common\MessageBus\Messages\Crawlers;

use App\Common\MessageBus\Messages\MessageInterface;
use App\Common\ValueObjects\GithubRepo;

class GithubContributorsToCrawlMessage implements MessageInterface
{
    private const CONTRIBUTORS_URL = 'https://github.com/%s/%s/graphs/contributors-data';

    private $repo;

    public function __construct(GithubRepo $repo)
    {
        $this->repo = $repo;
    }

    public function getRepo(): GithubRepo
    {
        return $this->repo;
    }

    public function getContributorsUrl(): string
    {
        $profile = $this->getRepo()->getProfile();
        $repo = $this->getRepo()->getName();

        return sprintf(self::CONTRIBUTORS_URL, $profile, $repo);
    }
}
