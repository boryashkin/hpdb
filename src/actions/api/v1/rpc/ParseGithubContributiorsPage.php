<?php

namespace app\actions\api\v1\rpc;

use app\abstracts\BaseAction;
use app\exceptions\github\UnableToSaveGithubProfile;
use app\messageBus\messages\crawlers\GithubContributorsToCrawlMessage;
use app\messageBus\messages\persistors\NewGithubProfileToPersistMessage;
use app\messageBus\repositories\GithubProfileRepository;
use app\messageBus\repositories\WebsiteGroupRepository;
use app\services\github\GithubProfileService;
use app\services\website\WebsiteGroupService;
use app\valueObjects\GithubRepo;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\SlimException;
use Symfony\Component\Messenger\MessageBusInterface;

class ParseGithubContributiorsPage extends BaseAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getParsedBody();
        $profile = isset($params['profile']) && \is_string($params['profile']) ? $params['profile'] : null;
        $project = isset($params['repo']) && \is_string($params['repo']) ? $params['repo'] : null;

        if ($profile === null || $project === null) {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['errors' => ['profile and project are mandatory fields']]));
            throw new SlimException($request, $response);
        }
        $repo = new GithubRepo($profile, $project);

        $service = new GithubProfileService(
            new GithubProfileRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO)),
            $this->getContainer()->get(CONTAINER_CONFIG_LOGGER),
            $this->getContainer()->get(CONTAINER_CONFIG_REDIS_CACHE)
        );
        $groupService = new WebsiteGroupService(
            new WebsiteGroupRepository($this->getContainer()->get(CONTAINER_CONFIG_MONGO)),
            $this->getContainer()->get(CONTAINER_CONFIG_REDIS_CACHE)
        );
        try {
            $github = $service->createOrAddOwnersRepo($repo);
            $groupService->createGroupByGithubRepo($repo);
        } catch (UnableToSaveGithubProfile $e) {
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(['errors' => ['Unable to save']]));
            throw new SlimException($request, $response);
        }

        $message = new GithubContributorsToCrawlMessage($repo);
        $messageNewGithubProfile = new NewGithubProfileToPersistMessage($github->login, new \DateTime(), null, $repo);

        /** @var MessageBusInterface $crawlerBus */
        $crawlerBus = $this->getContainer()->get(CONTAINER_CONFIG_REDIS_STREAM_CRAWLERS);
        /** @var MessageBusInterface $persistorsBus */
        $persistorsBus = $this->getContainer()->get(CONTAINER_CONFIG_REDIS_STREAM_PERSISTORS);

        $crawlerBus->dispatch($message);
        $persistorsBus->dispatch($messageNewGithubProfile);

        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response->getBody()->write(\json_encode(['saved' => true, 'github_profile' => $github]));

        return $response;
    }
}

