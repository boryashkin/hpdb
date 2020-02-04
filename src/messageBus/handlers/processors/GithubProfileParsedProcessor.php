<?php

namespace app\messageBus\handlers\processors;

use app\dto\github\GithubProfileDto;
use app\exceptions\InvalidUrlException;
use app\messageBus\messages\persistors\GithubProfileParsedToPersistMessage;
use app\messageBus\messages\persistors\NewWebsiteToPersistMessage;
use app\messageBus\messages\processors\GithubProfileParsedToProcessMessage;
use app\valueObjects\Url;
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
        if ($dto->blog) {
            try {
                $url = new Url($dto->blog);
                $newWebsiteMessage = new NewWebsiteToPersistMessage($url, 'github.profile', new \DateTime());
                $this->persistorsBus->dispatch($newWebsiteMessage);
            } catch (InvalidUrlException $e) {

            }
        }

        $newMessage = new GithubProfileParsedToPersistMessage($message->getGithubProfileId(), $dto);

        $this->persistorsBus->dispatch($newMessage);
    }
}
