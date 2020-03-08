<?php

namespace app\models;

use Jenssegers\Mongodb\Eloquent\Model;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

/**
 * @property ObjectId $_id
 * @property UTCDateTime $updated_at
 * @property bool $show_on_main
 * @property string $name
 * @property string $slug @idx/unique
 * @property string $description
 * @property string $logo
 * @property string $is_deleted
 */
class WebsiteGroup extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'websiteGroup';

    public function getShortName(): string
    {
        $name = $this->name;
        if (0 && strlen($this->name) > 30) {
            $name = substr($name, -30, 0);
            $name = substr($name, strpos(' '));
        }
        return $name;
    }
}
