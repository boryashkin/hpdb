<?php

declare(strict_types=1);

namespace App\Web\Admin\Category\Dto;

class AdminUserDataDto
{
    private $userHash;
    private $userIp;

    public function __construct(string $userHash, string $userIp)
    {
        $this->userHash = $userHash;
        $this->userIp = $userIp;
    }

    public function getUserHash(): string
    {
        return $this->userHash;
    }

    public function getUserIp(): string
    {
        return $this->userIp;
    }
}
