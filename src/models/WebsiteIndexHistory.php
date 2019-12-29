<?php
namespace app\models;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * @property ObjectId $_id
 * @property ObjectId $website_id
 * @property string $content
 * @property bool $is_http_only
 * @property int $http_status
 * @property array $http_headers
 * @property array $redirects
 * @property bool $available
 * @property float $time
 * @property UTCDateTime $createdAt
 */
class WebsiteIndexHistory extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'websiteIndexHistory';
}
