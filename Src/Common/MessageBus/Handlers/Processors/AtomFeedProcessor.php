<?php

namespace App\Common\MessageBus\Handlers\Processors;

use App\Common\MessageBus\Messages\Processors\XmlRssContentToProcessMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class AtomFeedProcessor implements ProcessorInterface
{
    private $name;
    /** @var MessageBusInterface */
    private $persistorsBus;

    public function __construct(string $name, MessageBusInterface $persistorsBus)
    {
        $this->name = $name;
        $this->persistorsBus = $persistorsBus;
        libxml_disable_entity_loader(true);
    }

    public function __invoke(XmlRssContentToProcessMessage $message)
    {

    }
}
