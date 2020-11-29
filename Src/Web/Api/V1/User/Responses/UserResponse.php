<?php

declare(strict_types=1);

namespace App\Web\Api\V1\User\Responses;

use App\Common\Models\User;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="User response",
 *     schema="UserResponse"
 * )
 */
class UserResponse
{
    /**
     * @OA\Property(
     *     title="email",
     *     example="borisd@hpdb.ru"
     * )
     * @var string
     */
    public $email;

    /**
     * @OA\Property(
     *     title="Created At",
     *     nullable=true,
     *     example="2020-11-22 08:26:01",
     *
     * )
     * @var string
     */
    public $createdAt;

    public static function createFromUser(User $user): self
    {
        $response = new self();
        $response->email = $user->email;
        $response->createdAt = $user->created_at ? $user->created_at->toDateTime()->format('Y-m-d H:i:s') : null;

        return $response;
    }
}
