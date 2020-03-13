<?php

namespace app\messageBus\repositories;

use Illuminate\Database\ConnectionInterface;

abstract class AbstractMongoRepository
{
    private $connection;

    /**
     * This is a hack to get mongodb work.
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }
}
