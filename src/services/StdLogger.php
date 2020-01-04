<?php

namespace app\services;

use Psr\Log\AbstractLogger;

class StdLogger extends AbstractLogger
{
    public function log($level, $message, array $context = array())
    {
        \error_log("StdLogger [$level]: $message");
    }
}
