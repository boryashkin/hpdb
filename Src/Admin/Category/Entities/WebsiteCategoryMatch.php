<?php

declare(strict_types=1);

namespace App\Admin\Category\Entities;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;

/**
 * @property ObjectId $website_id
 * @property int $category_code
 * @property string $voter_ip
 * @property int $user_hash
 */
class WebsiteCategoryMatch extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'adminWebsiteCategoryMatch';

    protected $fillable = [
        'website_id',
        'category_code',
        'voter_ip',
        'user_hash',
    ];
}
