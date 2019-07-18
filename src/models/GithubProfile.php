<?php
namespace app\models;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * @property ObjectId $_id
 * @property string $login
 * @property string $blog
 * @property string $followers_url
 * @property string $following_url
 * @property
 * @property UTCDateTime $updated_at
 */
class GithubProfile extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'githubProfile';
    /** @var array */
    protected $fillable = [
        'login',
        'blog',
        'avatar_url',
        'gravatar_id',
        'html_url',
        'location',
        'bio',
        'followers',
        'following',
    ];


}
