<?php

namespace app\messageBus\repositories;

use app\models\GithubProfile;
use Illuminate\Database\Eloquent\Model;
use MongoDB\BSON\ObjectId;

class GithubProfileRepository extends AbstractMongoRepository
{
    /**
     * @param ObjectId $id
     * @return GithubProfile|Model
     */
    public function getOne(ObjectId $id)
    {
        return GithubProfile::query()
            ->where('_id', '=', $id)
            ->first();
    }

    /**
     * @param string $login
     * @return GithubProfile|Model
     */
    public function getOneByLogin(string $login)
    {
        return GithubProfile::query()
            ->where('login', '=', $login)
            ->first();
    }

    public function save(GithubProfile $githubProfile): bool
    {
        return $githubProfile->save();
    }
}
