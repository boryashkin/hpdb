<?php

namespace App\Common\MessageBus\Handlers\Processors;

use App\Common\Dto\Github\GithubProfileDto;
use App\Common\Exceptions\InvalidUrlException;
use App\Common\MessageBus\Messages\Crawlers\GithubFollowersToCrawlMessage;
use App\Common\MessageBus\Messages\Persistors\GithubProfileParsedToPersistMessage;
use App\Common\MessageBus\Messages\Persistors\GithubProfileRepoMetaForGroupToPersistMessage;
use App\Common\MessageBus\Messages\Persistors\NewWebsiteToPersistMessage;
use App\Common\MessageBus\Messages\Processors\GithubProfileParsedToProcessMessage;
use App\Common\ValueObjects\Url;
use Symfony\Component\Messenger\MessageBusInterface;

class GithubProfileParsedProcessor implements ProcessorInterface
{
    /** @var MessageBusInterface */
    private $persistorsBus;
    /** @var MessageBusInterface */
    private $crawlersBus;
    private $name;

    public function __construct(string $name, MessageBusInterface $persistorsBus, MessageBusInterface $crawlersBus)
    {
        $this->name = $name;
        $this->persistorsBus = $persistorsBus;
        $this->crawlersBus = $crawlersBus;
    }

    public function __invoke(GithubProfileParsedToProcessMessage $message)
    {
        $json = $message->getContent()->content;
        $content = \json_decode($json, true);
        $dto = new GithubProfileDto($content);
        if ($dto->blog) {
            try {
                $url = new Url($dto->blog);
                $newWebsiteMessage = new NewWebsiteToPersistMessage(
                    $url,
                    'github.profile',
                    new \DateTime(),
                    $message->getGithubProfileId()
                );
                $this->persistorsBus->dispatch($newWebsiteMessage);
            } catch (InvalidUrlException $e) {
            }
        }
        if ($message->getRepo()) {
            try {
                $avatar = new Url($dto->avatar_url);
            } catch (InvalidUrlException $e) {
            }
            $repoMessage = new GithubProfileRepoMetaForGroupToPersistMessage(
                $message->getRepo(),
                $avatar ?? null,
                $dto->bio,
                new \DateTime()
            );
            $this->persistorsBus->dispatch($repoMessage);
        }
        if ($dto->followers_url) {
            $followMessage = new GithubFollowersToCrawlMessage(
                $message->getGithubProfileId(),
                new Url($dto->followers_url)
            );
            $this->crawlersBus->dispatch($followMessage);
        }

        $newMessage = new GithubProfileParsedToPersistMessage($message->getGithubProfileId(), $dto);

        $this->persistorsBus->dispatch($newMessage);
    }
}
