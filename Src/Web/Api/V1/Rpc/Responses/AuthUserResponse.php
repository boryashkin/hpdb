<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Rpc\Responses;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Auth User Response",
 *     schema="AuthUserResponse"
 * )
 */
class AuthUserResponse
{
    /**
     * @OA\Property(
     *     title="Bearer token",
     *     description="Use in Authorization header with Bearer prefix",
     *     example="uasd7ayosdihuasd69a87soh"
     * )
     * @var string
     */
    public $token;
}
