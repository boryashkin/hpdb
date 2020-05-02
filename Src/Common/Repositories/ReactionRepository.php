<?php

declare(strict_types=1);

namespace App\Common\Repositories;

use App\Common\Models\WebsiteReaction;
use Illuminate\Database\ConnectionInterface;
use MongoDB\BSON\ObjectId;

class ReactionRepository
{
    private $connection;

    /**
     * This is a hack to get mongodb work.
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function save(WebsiteReaction $reaction): bool
    {
        return $reaction->save();
    }

    public function calculateRawReactionsByWebsiteId(ObjectId $websiteId): array
    {
        $aggreagate = $this->connection->getCollection('websiteReaction')
            ->aggregate([
                ['$match' => ['website_id' => $websiteId]],
                [
                    '$group' => [
                        '_id' => ['reaction' => '$reaction'],
                        'count' => ['$sum' => 1],
                    ],
                ],
                ['$sort' => ['count' => -1]],
            ]);

        $reactions = [];
        foreach ($aggreagate as $item) {
            $reactions[$item->_id->reaction] = $item->count;
        }

        return $reactions;
    }
}
