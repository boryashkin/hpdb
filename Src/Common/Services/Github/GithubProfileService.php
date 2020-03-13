<?php

declare(strict_types=1);

namespace App\Common\Services\Github;

use App\Common\Exceptions\Github\UnableToSaveGithubProfile;
use App\Common\Repositories\GithubProfileRepository;
use App\Common\Models\GithubProfile;
use App\Common\ValueObjects\GithubRepo;
use MongoDB\BSON\ObjectId;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class GithubProfileService
{
    /** @var GithubProfileRepository */
    private $githubRepository;
    /** @var LoggerInterface */
    private $logger;
    /** @var CacheInterface */
    private $cache;

    public function __construct(GithubProfileRepository $repository, LoggerInterface $logger, CacheInterface $cache)
    {
        $this->githubRepository = $repository;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * @throws UnableToSaveGithubProfile
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function createOrAddOwnersRepo(GithubRepo $repo): GithubProfile
    {
        $github = $this->githubRepository->getOneByLogin($repo->getProfile());
        if ($github) {
            if (!$this->githubRepository->addRepo($github, $repo)) {
                throw new UnableToSaveGithubProfile();
            }
        } else {
            $github = new GithubProfile();
            $github->login = $repo->getProfile();
            $github->repos = [$repo->getName()];
            if (!$this->githubRepository->save($github)) {
                throw new UnableToSaveGithubProfile();
            }
        }
        $this->cache->get('github_profile_' . $github->_id, function (ItemInterface $item) use ($github) {
            $item->expiresAfter(600);

            return $github;
        });

        return $github;
    }

    /**
     * @throws UnableToSaveGithubProfile
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function upsertByLogin(string $login, ?GithubRepo $contributorTo): GithubProfile
    {
        $github = $this->githubRepository->getOneByLogin($login);
        if (!$github) {
            $github = new GithubProfile();
            $github->login = $login;
            if ($contributorTo) {
                $github->contributor_to = [(string)$contributorTo];
            }
            if (!$this->githubRepository->save($github)) {
                throw new UnableToSaveGithubProfile();
            }
        } elseif ($contributorTo) {
            if (!$this->githubRepository->addContributorTo($github, $contributorTo)) {
                throw new UnableToSaveGithubProfile();
            }
        }
        $this->cache->get('github_profile_' . $github->_id, function (ItemInterface $item) use ($github) {
            $item->expiresAfter(600);

            return $github;
        });

        return $github;
    }

    public function getOneById(ObjectId $id, bool $cached = true): GithubProfile
    {
        if ($cached) {
            $repo = $this->githubRepository;
            $github = $this->cache->get('github_profile_' . $id, function (ItemInterface $item) use ($repo, $id) {
                $item->expiresAfter(600);

                return $repo->getOne($id);
            });
            if ($github) {
                return $github;
            }
        }

        return $this->githubRepository->getOne($id);
    }
}
