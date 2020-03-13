<?php

namespace App\Common\Services;

use Psr\Log\AbstractLogger;

class StdLogger extends AbstractLogger
{
    public function log($level, $message, array $context = [])
    {
        \error_log("StdLogger [{$level}]: {$message}");
    }
}
