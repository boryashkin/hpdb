<?php

namespace app\messageBus\factories;

use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageBusFactory
{
    /** @var HandlerDescriptor[] */
    private $handlers;
    /** @var string[][] */
    private $senders;
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addHandler(string $messageType, HandlerDescriptor $descriptor): self
    {
        if (!isset($this->handlers[$messageType])) {
            $this->handlers[$messageType] = [];
        }
        $this->handlers[$messageType][] = $descriptor;

        return $this;
    }

    /**
     * @param string $messageType event type
     * @param string $senderAlias key from the Container where Symfony\Component\Messenger\Transport\Sender\SenderInterface is located
     *
     * @return $this
     */
    public function addSender(string $messageType, string $senderAlias): self
    {
        if (!isset($this->senders[$messageType])) {
            $this->senders[$messageType] = [];
        }

        $this->senders[$messageType][] = $senderAlias;

        return $this;
    }

    public function buildMessageBus(): MessageBusInterface
    {
        $middleware = [];
        if ($this->handlers) {
            $middleware[] = new \Symfony\Component\Messenger\Middleware\HandleMessageMiddleware(
                new \Symfony\Component\Messenger\Handler\HandlersLocator($this->handlers),
                false
            );
        }
        if ($this->senders) {
            $sendersLocator = new \Symfony\Component\Messenger\Transport\Sender\SendersLocator(
                $this->senders,
                $this->container
            );
            $middleware[] = new \Symfony\Component\Messenger\Middleware\SendMessageMiddleware($sendersLocator);
        }

        return new \Symfony\Component\Messenger\MessageBus($middleware);
    }
}
