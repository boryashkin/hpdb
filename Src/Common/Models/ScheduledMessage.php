<?php

namespace App\Common\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * @property ObjectId $_id
 * @property string $classname
 * @property int $status
 * @property string $serialized
 * @property UTCDateTime $run_at
 *
 * @property UTCDateTime $updated_at
 */
class ScheduledMessage extends Model
{
    public const STATUS_READY = 0;
    public const STATUS_TAKEN = 1;

    protected $connection = 'mongodb';
    protected $collection = 'scheduledMessage';
    /** @var array */
    protected $fillable = [
        'classname',
        'status',
        'serialized',
        'run_at',
    ];
}
