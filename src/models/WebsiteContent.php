<?php
namespace app\models;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;

/**
 * @property ObjectId $website_id
 * @property string $path
 * @property string $content
 * @property UTCDateTime $updated_at
 */
class WebsiteContent extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'websiteContent';
}
