<?php

declare(strict_types=1);

namespace App\Web\Api\V1\Feed\Responses\WebFeed;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="WebFeedResponseItem",
 *     description="Extracted rss feed item",
 *     title="Feed response item"
 * )
 */
class WebFeedResponseItem
{
    /**
     * @OA\Property(
     *     title="Title"
     * )
     *
     * @var string
     */
    public $title;

    /**
     * @OA\Property(
     *     title="Description"
     * )
     *
     * @var string
     */
    public $description;

    /**
     * @OA\Property(
     *     title="Website ID",
     *     example="5fa81efe60343c42e80b467f"
     * )
     *
     * @var string
     */
    public $websiteId;

    /**
     * @OA\Property(
     *     title="Language",
     *     example="en"
     * )
     *
     * @var string
     */
    public $language;

    /**
     * @OA\Property(
     *     title="Link"
     * )
     *
     * @var string
     */
    public $link;

    /**
     * @OA\Property(
     *     title="Host"
     * )
     *
     * @var string
     */
    public $host;

    /**
     * @OA\Property(
     *     title="Date",
     *     example="2030-06-01 13:00:00"
     * )
     *
     * @var string
     */
    public $date;
}
