<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Rpc\Builders;

use App\Common\Models\GithubProfile;
use App\Common\Models\WebsiteGroup;
use App\Common\ValueObjects\GithubRepo;
use App\Web\Api\V1\Rpc\Responses\GithubRepoResponse;

class RpcGithubResponseBuilder
{
    public function createOne(
        GithubProfile $githubProfile,
        GithubRepo $githubRepo,
        WebsiteGroup $group
    ): GithubRepoResponse
    {
        $response = new GithubRepoResponse();
        $response->githubProfileId = (string)$githubProfile->_id;
        $response->githubProfileLogin = $githubProfile->login;
        $response->repoName = $githubRepo->getName();
        $response->groupId = (string)$group->_id;
        $response->groupName = $group->name;

        return $response;
    }
}
