<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Rpc\Builders;

use App\Common\Models\GithubProfile;
use App\Common\Models\WebsiteGroup;
use App\Common\ValueObjects\GithubRepo;
use App\Web\Api\V1\Rpc\Responses\ParseGithubRepoResponse;

class RpcGithubResponseBuilder
{
    public function createOne(
        GithubProfile $githubProfile,
        GithubRepo $githubRepo,
        WebsiteGroup $group
    ): ParseGithubRepoResponse
    {
        $response = new ParseGithubRepoResponse();
        $response->githubProfileId = (string)$githubProfile->_id;
        $response->githubProfile = $githubProfile->login;
        $response->repoName = $githubRepo->getName();
        $response->groupId = (string)$group->_id;
        $response->groupName = $group->name;

        return $response;
    }
}
