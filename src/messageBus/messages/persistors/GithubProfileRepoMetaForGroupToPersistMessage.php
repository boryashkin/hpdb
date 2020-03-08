<?php

declare(strict_types=1);

namespace app\messageBus\messages\persistors;

use app\messageBus\messages\MessageInterface;
use app\valueObjects\GithubRepo;
use app\valueObjects\Url;
use DateTime;

class GithubProfileRepoMetaForGroupToPersistMessage implements MessageInterface
{
    /** @var \DateTime */
    private $dateFound;
    /** @var GithubRepo */
    private $repo;
    /** @var Url|null */
    private $avatarUrl;
    /** @var string|null */
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
