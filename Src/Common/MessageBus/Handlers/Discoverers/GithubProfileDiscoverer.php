<?php

namespace App\Common\MessageBus\Handlers\Discoverers;

use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Discovering new websites from github api
 * github.com/api/...
 */
class GithubProfileDiscoverer implements DiscovererInterface
{
    private $name;
    /** @var MessageBusInterface */
    private $publishBus;

    public function __construct(string $name, MessageBusInterface $publishBus)
    {
        $this->name = $name;
        $this->publishBus = $publishBus;
    }

    public function __invoke()
    {
        echo 'discovered' . PHP_EOL;
        //pass to the publi
    }
}
