<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Profile\Responses;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Profile item",
 *     schema="ProfileResponse"
 * )
 */
class WebsiteResponse
{
    /**
     * @OA\Property(
     *     title="ID",
     *     example="5fa81efe60343c42e80b467f"
     * )
     * @var string
     */
    public $id;

    /**
     * @OA\Property(
     *     title="Homepage URL",
     * )
     * @var string
     */
    public $homepage;

    /**
     * @OA\Property(
     *     title="Title of a homepage"
     * )
     * @var string
     */
    public $title;

    /**
     * @OA\Property(
     *     title="Meta description of a homepage"
     * )
     * @var string
     */
    public $description;

    /**
     * @OA\Property(
     *     title="Updated At",
     *     example="2020-11-22 08:26:01"
     * )
     * @var string
     */
    public $updatedAt;

    /**
     * @OA\Property(
     *     title="Reactions",
     *     ref="#/components/schemas/ProfileReactionsResponse"
     * )
     * @var string[]
     */
    public $reactions;
}
