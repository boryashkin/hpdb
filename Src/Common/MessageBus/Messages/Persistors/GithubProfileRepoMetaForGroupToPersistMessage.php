<?php

declare(strict_types=1);

namespace App\Common\MessageBus\Messages\Persistors;

use App\Common\MessageBus\Messages\MessageInterface;
use App\Common\ValueObjects\GithubRepo;
use App\Common\ValueObjects\Url;
use DateTime;

class GithubProfileRepoMetaForGroupToPersistMessage implements MessageInterface
{
    /** @var \DateTime */
    private $dateFound;
    /** @var GithubRepo */
    private $repo;
    /** @var null|Url */
    private $avatarUrl;
    /** @var null|string */
    private $bio;

    public function __construct(GithubRepo $repo, ?Url $avatarUrl, ?string $bio, DateTime $dateFound)
    {
        $this->repo = $repo;
        $this->dateFound = $dateFound;
        $this->avatarUrl = $avatarUrl;
        $this->bio = $bio;
    }

    public function getDateFound(): DateTime
    {
        return $this->dateFound;
    }

    public function getRepo(): GithubRepo
    {
        return $this->repo;
    }

    public function getAvatarUrl(): ?Url
    {
        return $this->avatarUrl;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }
}
