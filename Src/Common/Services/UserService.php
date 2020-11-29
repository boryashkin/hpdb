<?php

declare(strict_types=1);

namespace App\Common\Services;

use Ahc\Jwt\JWT;
use Ahc\Jwt\JWTException;
use App\Common\Models\User;
use App\Common\Repositories\UserRepository;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\InvalidArgumentException;

class UserService
{
    private const PASSWORD_HASH = PASSWORD_BCRYPT;
    private const TOKEN_HASH = PASSWORD_BCRYPT;
    private const JWT_ALGO = 'HS256';
    private const JWT_HASH_KEY = 'hash';
    private const JWT_UID_KEY = 'uid';

    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getOne(ObjectId $id): ?User
    {
        return $this->userRepository->getOne($id);
    }

    public function getOneByEmail(string $email): ?User
    {
        return $this->userRepository->getOneByEmail($email);
    }

    public function save(User $user): bool
    {
        $user->password = password_hash($user->password, UserService::PASSWORD_HASH);

        return $this->userRepository->save($user);
    }

    public function isPasswordValid(string $password, User $user): bool
    {
        return password_verify($password, $user->password);
    }

    public function getNewAuthToken(User $user): string
    {
        $token = password_hash(uniqid('', true), UserService::TOKEN_HASH);
        $this->userRepository->addAuthToken($user, $token);

        $jwt = new JWT('secret', UserService::JWT_ALGO, 60 * 60 * 24 * 30, 10);

        return $jwt->encode([
            self::JWT_UID_KEY => (string)$user->_id,
            self::JWT_HASH_KEY => $token,
            'aud' => 'hpdb.ru',
            'scopes' => ['*'],
            'iss' => 'https://hpdb.ru',
        ]);
    }

    public function getUserByBearerToken(string $bearer): ?User
    {
        $jwt = new JWT('secret', UserService::JWT_ALGO, 60 * 60 * 24 * 30, 10);

        try {
            $decoded = $jwt->decode($bearer, true);
        } catch (JWTException $e) {
            return null;
        }
        if (!isset($decoded[self::JWT_HASH_KEY]) || !is_string($decoded[self::JWT_HASH_KEY])) {
            return null;
        }
        if (!isset($decoded[self::JWT_UID_KEY]) || !is_string($decoded[self::JWT_UID_KEY])) {
            return null;
        }

        try {
            $userId = new ObjectId($decoded[self::JWT_UID_KEY]);
        } catch (InvalidArgumentException $e) {
            error_log('Passed invalid ObjectId in JWT');

            return null;
        }


        $authToken = $decoded[self::JWT_HASH_KEY];
        $user = $this->getOne($userId);
        if (!$user) {
            return null;
        }

        $tokens = $user->auth_tokens;
        if (!in_array($authToken, $tokens, true)) {
            return null;
        }

        return $user;
    }
}



