<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Group\Responses;

/**
 * @OA\Schema(
 *     title="Group item",
 *     schema="GroupResponse"
 * )
 */
class GroupResponse
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
     *     title="Updated At",
     *     example="2020-11-22 08:26:01"
     * )
     * @var string
     */
    public $updatedAt;

    /**
     * @OA\Property(
     *     title="Show On Main"
     * )
     * @var bool
     */
    public $showOnMain;

    /**
     * @OA\Property(
     *     title="Name"
     * )
     * @var string
     */
    public $name;

    /**
     * @OA\Property(
     *     title="Slug"
     * )
     * @var string
     */
    public $slug;

    /**
     * @OA\Property(
     *     title="Description"
     * )
     * @var string
     */
    public $description;

    /**
     * @OA\Property(
     *     title="Logo URL"
     * )
     * @var string
     */
    public $logo;
}
