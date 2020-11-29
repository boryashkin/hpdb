<?php

declare(strict_types=1);

namespace App\Common\Repositories;

use App\Common\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MongoDB\BSON\ObjectId;

class UserRepository extends AbstractMongoRepository
{
    /**
     * @param ObjectId $id
     * @return Builder|Model|User|null
     */
    public function getOne(ObjectId $id)
    {
        return User::query()
            ->where('_id', '=', $id)
            ->first();
    }

    /**
     * @param string $email
     * @return Builder|Model|User|null
     */
    public function getOneByEmail(string $email)
    {
        return User::query()
            ->where('email', '=', $email)
            ->limit(1)
            ->first();
    }

    public function save(User $user): bool
    {
        return $user->save();
    }

    public function addAuthToken(User $user, string $token): bool
    {
        $saved = false;
        if (!$user->auth_tokens || (\is_array($user->auth_tokens) && !\in_array($token, $user->auth_tokens))) {
            $user->auth_tokens = \array_merge($user->auth_tokens ?? [], [$token]);
            $saved = $user->update(['auth_tokens' => $user->auth_tokens]);
        }

        return $saved;
    }
}
