<?php
namespace app\models;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * @property ObjectId $_id
 * @property string $github_profile_id
 * @property UTCDateTime $updated_at
 */
class GithubProfileRelation extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'githubProfileRelation';
}
