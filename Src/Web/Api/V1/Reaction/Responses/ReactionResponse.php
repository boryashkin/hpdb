<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Reaction\Responses;

/**
 * @OA\Schema(
 *     title="Reaction response",
 *     schema="ReactionResponse"
 * )
 */
class ReactionResponse
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
     *     title="Reaction",
     *     example="Like"
     * )
     * @var string
     */
    public $reaction;

    /**
     * @OA\Property(
     *     title="Created At",
     *     example="2020-11-22 08:26:01"
     * )
     * @var string
     */
    public $createdAt;
}
