<?php

namespace app\messageBus\messages\persistors;

use app\messageBus\messages\MessageInterface;
use DateTime;

class NewGithubProfileToPersistMessage implements MessageInterface
{
    /** @var string */
    private $login;
    /** @var string */
    private $source;
    /** @var \DateTime */
    private $dateFound;

    public function __construct(string $login, DateTime $dateFound)
    {
        $this->login = $login;
        $this->dateFound = $dateFound;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getDateFound(): DateTime
    {
        return $this->dateFound;
    }
}
