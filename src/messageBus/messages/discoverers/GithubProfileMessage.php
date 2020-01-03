<?php

namespace app\messageBus\messages\discoverers;

use app\messageBus\messages\MessageInterface;

class GithubProfileMessage implements MessageInterface
{
    private $website;
    private $githubProfile;

    public function __construct(string $website, string $githubProfile)
    {
        $this->website = $website;
        $this->githubProfile = $githubProfile;
    }
}
