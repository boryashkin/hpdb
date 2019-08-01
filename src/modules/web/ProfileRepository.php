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

    public function getList(int $page, $query = null)
    {
        $q = strip_tags($query);
        if ($page <= 0) {
            $page = 1;
        }
        $step = 30;
        $from = ($page - 1) * $step;

        $req = Website::query()
            ->select(['profile_id', 'homepage'])
            ->offset($from)->limit($step);
        if ($query) {
            $req->where('homepage', 'like', '%' . $q . '%');
        }
        $websites = $req->get();

        return $websites->toArray();
    }

    /**
     * @param int $id
     * @return Website
     */
    public function getOne(int $id)
    {
        return Website::query()
            ->where('profile_id', '=', $id)
            ->get()->all()[0];
    }
}