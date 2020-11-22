<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Rpc\Responses;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Github Repo Parsing Response",
 *     schema="ParseGithubRepoResponse"
 * )
 */
class ParseGithubRepoResponse
{
    /**
     * @OA\Property(
     *     title="Repo Name"
     * )
     * @var string
     */
    public $repoName;

    /**
     * @OA\Property(
     *     title="Github Profile ID"
     * )
     * @var string
     */
    public $githubProfileId;

    /**
     * @OA\Property(
     *     title="Github Profile"
     * )
     * @var string
     */
    public $githubProfile;

    /**
     * @OA\Property(
     *     title="Group ID",
     *     description="ID of a Group, created for contributors"
     * )
     * @var string
     */
    public $groupId;

    /**
     * @OA\Property(
     *     title="Group Name",
     *     description="Name of a Group"
     * )
     * @var string
     */
    public $groupName;
}
