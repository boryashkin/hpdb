<?php

namespace App\Common\Services;

use Psr\Log\AbstractLogger;

class StdLogger extends AbstractLogger
{
    private $enableDebug = true;

    public function __construct(bool $enableDebug = true)
    {
        $this->enableDebug = $enableDebug;
    }

    public function log($level, $message, array $context = []): void
    {
        \error_log("StdLogger [{$level}]: {$message}");
    }

    public function debug($message, array $context = array()): void
    {
        if (!$this->enableDebug) {
            return;
        }
        parent::debug($message, $context);
    }
}
