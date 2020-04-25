<?php

declare(strict_types=1);

namespace App\Common\Dictionaries;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * @property ObjectId $_id
 * @property string $name
 * @property string $count
 * @property UTCDateTime $updated_at
 */
class WebsiteTag extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'websiteTag';

    public function getFillable(): array
    {
        return [
            'name',
            'count',
        ];
    }
}
