<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Profile\Responses;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Profile reactions",
 *     schema="ProfileReactionsResponse"
 * )
 */
class WebsiteReactionsResponse
{
    /**
     * @OA\Property(
     *     title="Not a homepage"
     * )
     * @var int
     */
    public $nohp;

    /**
     * @OA\Property(
     *     title="Like",
     * )
     * @var int
     */
    public $like;

    /**
     * @OA\Property(
     *     title="Dislike",
     * )
     * @var int
     */
    public $dislike;
}
