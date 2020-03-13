<?php

namespace app\messageBus\messages\persistors;

use app\messageBus\messages\MessageInterface;
use app\valueObjects\GithubRepo;
use DateTime;

class NewGithubProfileToPersistMessage implements MessageInterface
{
    /** @var string */
    private $login;
    /** @var GithubRepo */
    private $contributorTo;
    /** @var \DateTime */
    private $dateFound;
    /** @var null|GithubRepo */
    private $repo;

    public function __construct(string $login, DateTime $dateFound, ?GithubRepo $contributorTo, ?GithubRepo $repo)
    {
        $this->login = $login;
        $this->repo = $repo;
        $this->dateFound = $dateFound;
        $this->contributorTo = $contributorTo;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getDateFound(): DateTime
    {
        return $this->dateFound;
    }

    public function getContributorTo(): ?GithubRepo
    {
        return $this->contributorTo;
    }

    public function getRepo(): ?GithubRepo
    {
        return $this->repo;
    }
}
