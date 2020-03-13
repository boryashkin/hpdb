<?php

namespace App\Common\MessageBus\Messages\Discoverers;

use App\Common\MessageBus\Messages\MessageInterface;

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
