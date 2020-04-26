<?php

declare(strict_types=1);

namespace App\Common\Services;

class IpCheckService
{
    private $allowedIp;

    public function __construct(string $allowedIp)
    {
        $this->allowedIp = $allowedIp;
    }

    public function checkIp(string $ipToCheck): bool
    {
        if ($this->allowedIp == '*' || $this->allowedIp == '*.*.*.*') {
            return TRUE;
        }
        if ($ipToCheck == $this->allowedIp) {
            return TRUE;
        }
        $mask = str_replace('.*', '', $this->allowedIp);

        return strpos($ipToCheck, $mask) === 0;
    }
}
