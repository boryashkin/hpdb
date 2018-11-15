<?php

require __DIR__ . '/../../vendor/autoload.php';

$config = require __DIR__ . '/dbconfig.php';

$clickhouse = new ClickHouseDB\Client($config);
$clickhouse->database('default');
$clickhouse->setConnectTimeOut(1);
$repo = new \app\modules\web\ProfileRepository();
$client = new \GuzzleHttp\Client();

$page = 1;
while ($sites = $repo->getList(null, $page++)) {
    foreach ($sites as $site) {
        $id = $sites['profile_id'];
        $host = $sites['homepage'];
        $alive = false;
        try {
            $response = $client->get($host, ['connect_timeout' => 5]);
            @file_put_contents(__DIR__ . '/../../homepages/' . md5($host) . '.html', $response->getBody());
            $alive = true;
        } catch (Throwable $e) {
            unset($e);
            $alive = false;
        }
        $clickhouse->insert('host_alive_status',
            [
                [time(), $id, $alive],
            ],
            ['event_time', 'site_id', 'alive']
        );
    }
}
