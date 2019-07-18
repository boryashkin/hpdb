<?php
namespace app\models;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * @property ObjectId $_id
 * @property int $profile_id
 * @property string $homepage
 * @property bool $is_http_only
 * @property UTCDateTime $updated_at
 */
class Website extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'website';

    public function content()
    {
        return $this->hasOne(WebsiteContent::class)->orderBy('created_at', 'desc');
    }
}
