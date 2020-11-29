<?php

declare(strict_types=1);

namespace App\Common\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * @property ObjectId $_id
 * @property string $email @idx
 * @property string $password
 * @property string[] $auth_tokens @idx
 * @property UTCDateTime $created_at
 * @property UTCDateTime $updated_at
 */
class User extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'user';

    public function getFillable()
    {
        return [
            'email',
        ];
    }
}
