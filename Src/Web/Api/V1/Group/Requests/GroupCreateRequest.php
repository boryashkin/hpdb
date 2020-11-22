<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Group\Requests;

/**
 * @OA\Schema(
 *     title="Group creation request",
 *     schema="GroupCreateRequest"
 * )
 */
class GroupCreateRequest
{
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
     *     title="Logo URL"
     * )
     * @var string
     */
    public $logo;

    /**
     * @OA\Property(
     *     title="Description"
     * )
     * @var string
     */
    public $description;

    /**
     * @OA\Property(
     *     title="Show the group on main page"
     * )
     * @var bool
     */
    public $showOnMain;
}
