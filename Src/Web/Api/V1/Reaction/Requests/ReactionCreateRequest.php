<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Reaction\Requests;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Reaction creation request",
 *     schema="ReactionCreateRequest"
 * )
 */
class ReactionCreateRequest
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
     *     title="Reaction name",
     *     enum={"nohp", "like", "dislike"}
     * )
     * @var string
     */
    public $reaction;
}
