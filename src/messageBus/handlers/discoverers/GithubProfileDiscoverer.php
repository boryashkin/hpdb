<?php

namespace app\messageBus\handlers\discoverers;

use app\messageBus\messages\crawlers\WebsiteMessage;
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
        $message = new WebsiteMessage('borisd.ru');

        $this->publishBus->dispatch($message);
    }
}
