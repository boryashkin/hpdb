<?php

namespace app\models;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;

/**
 * @property ObjectId $website_id
 * @property string $title
 * @property string $description
 * @property ObjectId $from_website_index_history_id
 * @property UTCDateTime $updated_at
 */
class WebsiteContent extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'websiteContent';

    public function getFillable()
    {
        return [
            'website_id',
            'title',
            'description',
            'from_website_index_history_id',
        ];
    }
}
