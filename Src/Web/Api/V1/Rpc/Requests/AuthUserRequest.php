<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Rpc\Requests;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Auth user request",
 *     schema="AuthUserRequest"
 * )
 */
class AuthUserRequest
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
     *     title="Password"
     * )
     * @var string
     */
    public $password;
}
