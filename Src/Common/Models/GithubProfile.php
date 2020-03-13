<?php

namespace App\Common\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * @property ObjectId $_id
 * @property int $id
 * @property string $node_id
 * @property string $avatar_url
 * @property string $gravatar_id
 * @property string $url
 * @property string $html_url
 * @property string $gists_url
 * @property string $starred_url
 * @property string $subscriptions_url
 * @property string $organizations_url
 * @property string $repos_url
 * @property string $events_url
 * @property string $received_events_url
 * @property string $type
 * @property bool $site_admin
 * @property string $name
 * @property string $company
 * @property string $location
 * @property string $email
 * @property bool $hireable
 * @property string $bio
 * @property int $public_repos
 * @property int $public_gists
 * @property int $followers
 * @property int $following
 * @property string $login
 * @property string $blog
 * @property string $followers_url
 * @property string $following_url
 * @property UTCDateTime $updated_at
 * @property int $parsing_status
 * @property ObjectId[] $contributor_to
 * @property string[] $repos
 */
class GithubProfile extends Model
{
    public const PARSING_STATUS_NEW = 0;
    //@todo: save this status if blog wasn't found, to periodically recheck an existence
    public const PARSING_STATUS_NO_BLOG_FOUND = 1;

    protected $connection = 'mongodb';
    protected $collection = 'githubProfile';
    /** @var array */
    protected $fillable = [
        'id',
        'node_id',
        'avatar_url',
        'gravatar_id',
        'url',
        'html_url',
        'gists_url',
        'starred_url',
        'subscriptions_url',
        'organizations_url',
        'repos_url',
        'events_url',
        'received_events_url',
        'type',
        'site_admin',
        'name',
        'company',
        'location',
        'email',
        'hireable',
        'bio',
        'public_repos',
        'public_gists',
        'followers',
        'following',
        'login',
        'blog',
        'followers_url',
        'following_url',
        'contributor_to',
        'repos',
    ];
}
