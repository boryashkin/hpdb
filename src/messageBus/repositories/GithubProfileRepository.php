<?php

namespace app\messageBus\repositories;

use app\models\GithubProfile;
use app\valueObjects\GithubRepo;
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

    public function addContributorTo(GithubProfile $github, GithubRepo $repo): bool
    {
        $contributorToStr = (string)$repo;
        $saved = true;
        if (!$github->contributor_to || (\is_array($github->contributor_to) && !\in_array($contributorToStr, $github->contributor_to))) {
            $github->contributor_to = \array_merge($github->contributor_to ?? [], [$contributorToStr]);
            $saved = $github->update(['contributor_to' => $github->contributor_to]);
        }

        return $saved;
    }

    public function addRepo(GithubProfile $github, GithubRepo $repo): bool
    {
        $repoStr = $repo->getName();
        $saved = true;
        if (!$github->repos || (\is_array($github->repos) && !\in_array($repoStr, $github->repos))) {
            $github->repos = \array_merge($github->repos ?? [], [$repoStr]);
            $saved = $github->update(['repos' => $github->repos]);
        }

        return $saved;
    }
}
