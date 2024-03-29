<?php

namespace App\Common\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * @property ObjectId $_id
 * @property UTCDateTime $updated_at
 * @property bool $show_on_main
 * @property string $name
 * @property string $slug         @idx/unique
 * @property string $description
 * @property string $logo
 * @property string $is_deleted
 * @property string $owner_id     @idx
 */
class WebsiteGroup extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'websiteGroup';

    public function getFillable(): array
    {
        return [
            'name',
            'show_on_main',
            'description',
            'logo',
        ];
    }
}
