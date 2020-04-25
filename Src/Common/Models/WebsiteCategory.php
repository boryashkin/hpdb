<?php

declare(strict_types=1);

namespace App\Common\Dictionaries;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * @property ObjectId $_id
 * @property string $name
 * @property string $code @index(unique)
 * @property string $count
 * @property UTCDateTime $updated_at
 */
class WebsiteCategory extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'websiteCategory';

    public const CODE_PERSONAL = 0;
    public const CODE_COMMERCIAL = 1;
    public const CODE_GOVERNMENT = 2;
    public const CODE_NON_PROFIT = 3;

    public function getFillable(): array
    {
        return [
            'name',
            'code',
            'count',
        ];
    }
}
