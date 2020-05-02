<?php

declare(strict_types=1);

namespace App\Common\Adapters;

use App\Common\Exceptions\NotImplementedException;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Events\Dispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class IlluminateDispatcherAdapter extends Dispatcher
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(ContainerContract $container = null)
    {
        parent::__construct($container);
    }

    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->eventDispatcher = $dispatcher;
    }

    public function listen($events, $listener)
    {
        if (!is_string($events)) {
            throw new NotImplementedException('Events should be a string');
        }
        $this->eventDispatcher->addListener($events, $listener);
    }

    public function dispatch($event, $payload = [], $halt = false)
    {
        $this->eventDispatcher->dispatch($event);
    }
}
