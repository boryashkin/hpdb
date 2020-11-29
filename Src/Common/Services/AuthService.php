<?php

declare(strict_types=1);

namespace App\Common\Services;

use App\Common\Models\User;
use MongoDB\BSON\ObjectId;

class AuthService
{
    private const CURRENT_USER_ID = 'as_current_user_id';

    /** @var UserService */
    private $userService;
    /** @var LocalSessionCache */
    private $localSessionCache;

    public function __construct(LocalSessionCache $localSessionCache, UserService $userService)
    {
        $this->localSessionCache = $localSessionCache;
        $this->userService = $userService;
    }

    public function setCurrentUserId(ObjectId $id): void
    {
        $this->localSessionCache->set(self::CURRENT_USER_ID, $id);
    }

    public function getCurrentUserId(): ?ObjectId
    {
        $objectId = $this->localSessionCache->get(self::CURRENT_USER_ID);
        if ($objectId !== null && !$objectId instanceof ObjectId) {
            return null;
        }

        return $objectId;
    }

    public function getCurrentUser(): ?User
    {
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            return null;
        }

        return $this->userService->getOne($userId);
    }
}
