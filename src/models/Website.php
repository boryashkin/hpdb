<?php
namespace app\models;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * @property ObjectId $_id
 * @property int $profile_id @deprecated, now profile_id on the api and web must be == _id
 * @property string $homepage
 * @property bool $is_http_only
 * @property UTCDateTime $updated_at
 * @property string $title
 * @property string $description
 * @property ObjectId[] $groups
 */
class Website extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'website';

    public function content()
    {
        return $this->hasOne(WebsiteContent::class)->orderBy('created_at', 'desc');
    }

    public function isHttps()
    {
        return \stripos($this->homepage, 'https:') === 0;
    }

    public function getFillable()
    {
        return [
            'homepage',
            'is_http_only',
            'groups',
            'profile_id',
            'title',
            'description',
        ];
    }
}
