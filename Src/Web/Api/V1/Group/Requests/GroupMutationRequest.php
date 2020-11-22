<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Group\Requests;

/**
 * @OA\Schema(
 *     title="Group mutation request",
 *     schema="GroupMutationRequest"
 * )
 */
class GroupMutationRequest extends GroupCreateRequest
{
    /**
     * @OA\Property(
     *     format="string",
     *     title="ID",
     *     example="5fa81efe60343c42e80b467f"
     * )
     * @var string
     */
    public $id;

    /**
     * @OA\Property(
     *     title="Slug",
     *     description="Not editable yet"
     * )
     * @var string
     */
    public $slug;
}
