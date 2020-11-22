<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Rpc\Requests;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Github repo contributors parsing request",
 *     schema="ParseGithubRepoRequest"
 * )
 */
class ParseGithubRepoRequest
{
    /**
     * @OA\Property(
     *     title="Github Profile",
     *     example="apple"
     * )
     * @var string
     */
    public $profile;

    /**
     * @OA\Property(
     *     title="Repo",
     *     example="swift"
     * )
     * @var string
     */
    public $repo;
}
