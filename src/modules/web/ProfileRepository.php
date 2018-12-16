<?php
namespace app\modules\web;

class ProfileRepository
{
    private const DB_NAME = __DIR__ . '/../../../domainslibrary.db';
    private const DB_PREFIX = 'all_';

    public function getList($query, int $page)
    {
        $db = new \SQLite3(self::DB_NAME);
        $q = strip_tags($query);
        if ($page <= 0) {
            $page = 1;
        }
        $step = 30;
        $from = ($page - 1) * $step;
        $to = ($page - 1) + $step;

        if ($query) {
            $where = 'where `homepage` LIKE :homepage';
        } else {
            $where = '';
        }
        $stmt = $db->prepare(
            'SELECT * from `' . self::DB_PREFIX . 'profiles` 
            ' . $where . ' LIMIT ' . $from . ', ' . $to
        );
        if ($where) {
            $stmt->bindValue(':homepage', '%' . $q . '%', SQLITE3_TEXT);
        }

        $result = $stmt->execute();
        while ($row = $result->fetchArray())
        {
            $domainslist[$row['profile_id']] = $row['homepage'];
        }
        $stmt->close();
        $db->close();

        return $domainslist;
    }

    public function getOne($id)
    {
        $db = new \SQLite3(self::DB_NAME);

        $stmt = $db->prepare(
            'SELECT * from `' . self::DB_PREFIX . 'profiles` 
            WHERE profile_id = :id LIMIT 1'
        );
        $stmt->bindValue(':id', $id);

        $result = $stmt->execute();
        $row = $result->fetchArray();
        $stmt->close();
        $db->close();

        return $row;
    }
}