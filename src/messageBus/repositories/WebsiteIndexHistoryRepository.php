<?php

namespace app\messageBus\repositories;

use app\models\WebsiteIndexHistory;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use MongoDB\BSON\ObjectId;

class WebsiteIndexHistoryRepository
{
    private $connection;

    /**
     * This is a hack to get mongodb work
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param ObjectId $id
     * @return WebsiteIndexHistory|Model
     */
    public function getOne(ObjectId $id)
    {
        return WebsiteIndexHistory::query()
            ->where('_id', '=', $id)
            ->first();
    }
}
