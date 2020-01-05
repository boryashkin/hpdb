<?php

namespace app\messageBus\repositories;

use Illuminate\Database\ConnectionInterface;

abstract class AbstractMongoRepository
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
}
