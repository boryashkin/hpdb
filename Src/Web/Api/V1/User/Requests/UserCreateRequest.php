<?php

declare(strict_types=1);

namespace App\Web\Api\V1\User\Requests;

/**
 * @OA\Schema(
 *     title="User creation request",
 *     schema="UserCreateRequest"
 * )
 */
class UserCreateRequest
{
    /**
     * @OA\Property(
     *     title="Email"
     * )
     * @var string
     */
    public $email;

    /**
     * @OA\Property(
     *     title="Password"
     * )
     * @var string
     */
    public $password;
}
