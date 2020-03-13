<?php

namespace App\Common\MessageBus\Handlers\Processors;

use App\Common\Dto\Github\GithubFollowerDto;
use App\Common\MessageBus\Messages\Persistors\GithubFollowerParsedToPersistMessage;
use App\Common\MessageBus\Messages\Processors\GithubFollowersToProcessMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class GithubFollowersParsedProcessor implements ProcessorInterface
{
    /** @var MessageBusInterface */
    private $persistorsBus;
    private $name;

    public function __construct(string $name, MessageBusInterface $persistorsBus)
    {
        $this->name = $name;
        $this->persistorsBus = $persistorsBus;
    }

    public function __invoke(GithubFollowersToProcessMessage $message)
    {
        $json = $message->getContent()->content;
        $content = \json_decode($json, true);
        foreach ($content as $key => $follower) {
            $dto = new GithubFollowerDto($follower);
            $newMessage = new GithubFollowerParsedToPersistMessage($dto);

            $this->persistorsBus->dispatch($newMessage);
        }
    }
}
