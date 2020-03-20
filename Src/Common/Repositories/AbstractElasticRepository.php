<?php

declare(strict_types=1);

namespace App\Common\Repositories;

use Elasticsearch\Client;

abstract class AbstractElasticRepository
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    final protected function getClient(): Client
    {
        return $this->client;
    }

    abstract public static function getIndex(): string;
}
