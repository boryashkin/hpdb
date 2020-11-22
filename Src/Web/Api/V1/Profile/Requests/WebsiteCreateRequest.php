<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Profile\Requests;

/**
 * @OA\Schema(
 *     title="Profile creation request",
 *     schema="ProfileCreateRequest"
 * )
 */
class WebsiteCreateRequest
{
    /**
     * @OA\Property(
     *     title="Website URL"
     * )
     * @var string
     */
    public $website;
}
