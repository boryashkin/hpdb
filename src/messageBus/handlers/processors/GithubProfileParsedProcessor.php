<?php

namespace app\messageBus\handlers\processors;

use app\dto\github\GithubProfileDto;
use app\messageBus\messages\persistors\GithubProfileParsedToPersistMessage;
use app\messageBus\messages\processors\GithubProfileParsedToProcessMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class GithubProfileParsedProcessor implements ProcessorInterface
{
    /** @var MessageBusInterface */
    private $persistorsBus;
    private $name;

    public function __construct(string $name, MessageBusInterface $persistorsBus)
    {
        $this->name = $name;
        $this->persistorsBus = $persistorsBus;
    }

    public function __invoke(GithubProfileParsedToProcessMessage $message)
    {
        $json = $message->getContent()->content;
        $content = \json_decode($json, true);
        $dto = new GithubProfileDto($content);
        //todo: parse followers to crawl and persist

        $newMessage = new GithubProfileParsedToPersistMessage($message->getGithubProfileId(), $dto);

        $this->persistorsBus->dispatch($newMessage);
    }
}
