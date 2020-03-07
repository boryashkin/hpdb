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

    public function __construct(string $login, DateTime $dateFound, ?GithubRepo $contributorTo)
    {
        $this->login = $login;
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
}
