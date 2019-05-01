<?php
namespace app\models;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property int $profile_id
 * @property string $homepage
 */
class Website extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'website';
}
