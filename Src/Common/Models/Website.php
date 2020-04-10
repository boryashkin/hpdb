<?php

namespace App\Common\Models;

use App\Common\Dto\Website\WebsiteWebFeedEmbedded;
use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * @property ObjectId $_id
 * @property string $homepage
 * @property string $scheme
 * @property UTCDateTime $updated_at
 * @property WebsiteContent $content
 * @property ObjectId[] $groups
 * @property ObjectId $github_profile_id
 * @property WebsiteWebFeedEmbedded[] $web_feeds
 */
class Website extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'website';

    public function isHttps()
    {
        return $this->scheme === 'https';
    }

    public function getFillable()
    {
        return [
            'homepage',
            'scheme',
            'content',
            'groups',
            'title',
            'description',
            'github_profile_id',
            'web_feeds',
        ];
    }
}
