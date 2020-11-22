<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Rpc\Responses;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Website Group",
 *     schema="WebsiteGroupsResponse"
 * )
 */
class WebsiteGroupsResponse
{
    /**
     * @OA\Property(
     *     title="Profile ID",
     *     example="5fa81efe60343c42e80b467f"
     * )
     * @var string
     */
    public $id;

    /**
     * @OA\Property(
     *     title="Profile Group IDs",
     *     description="List of groups of a profile "
     * )
     * @var string[]
     */
    public $groups;
}
