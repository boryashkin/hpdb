<?php

declare(strict_types=1);

namespace App\Common\Services\Scheduled;

use App\Common\MessageBus\Messages\MessageInterface;

class Base64Serializer
{
    public function serialize(MessageInterface $message): string
    {
        return \base64_encode(\serialize($message));
    }

    public function unserialize(string $message): MessageInterface
    {
        return \unserialize(\base64_decode($message));
    }
}
