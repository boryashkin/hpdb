<?php

namespace App\Common\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * @property ObjectId $_id
 * @property ObjectId $website_id
 * @property string $reaction
 * @property string $ip
 * @property string $user_agent
 * @property ObjectId $user_id
 * @property UTCDateTime $created_at
 */
class WebsiteReaction extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'websiteReaction';
}
