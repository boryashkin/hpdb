<?php

namespace app\messageBus\messages\crawlers;

use app\messageBus\messages\MessageInterface;

class WebsiteMessage implements MessageInterface
{
    private $data;

    public function __construct(string $data)
    {
        $this->data = $data;
    }

    public function getData(): string
    {
        return $this->data;
    }
}
