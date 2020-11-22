<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Rpc\Requests;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Add website to group request",
 *     schema="AddWebsiteToGroupRequest"
 * )
 */
class AddWebsiteToGroupRequest
{
    /**
     * @OA\Property(
     *     title="Profile ID",
     *     example="5fa81efe60343c42e80b467f"
     * )
     * @var string
     */
    public $websiteId;

    /**
     * @OA\Property(
     *     title="Group ID",
     *     example="5fa81efe60343c42e80b467f"
     * )
     * @var string
     */
    public $groupId;
}
