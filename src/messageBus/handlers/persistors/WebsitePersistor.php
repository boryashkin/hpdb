<?php

namespace app\messageBus\handlers\persistors;

class WebsitePersistor implements PersistorInterface
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __invoke()
    {
        echo 'PERSISTED;' . PHP_EOL;
    }
}
