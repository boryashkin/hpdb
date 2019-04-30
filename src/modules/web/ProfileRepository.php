<?php
namespace app\modules\web;

use app\models\Website;
use Illuminate\Database\ConnectionInterface;

class ProfileRepository
{
    private $connection;

    /**
     * This is a hack to get mongodb work
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function getList($query, int $page)
    {
        $q = strip_tags($query);
        if ($page <= 0) {
            $page = 1;
        }
        $step = 30;
        $from = ($page - 1) * $step;

        $websites = Website::query()
            ->select(['profile_id', 'homepage'])
            ->where('homepage', 'like', '%' . $q . '%')
            ->offset($from)->limit($step)
            ->get();
        return $websites->toArray();
    }

    public function getOne(int $id)
    {
        return Website::query()
            ->where('profile_id', '=', (string)$id)
            ->get()->all()[0];
    }
}