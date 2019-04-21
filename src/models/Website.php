<?php
namespace app\models;

use Jenssegers\Mongodb\Eloquent\Model;

class Website extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'website';
}
