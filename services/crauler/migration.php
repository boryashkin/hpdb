<?php

require __DIR__ . '/../../vendor/autoload.php';

$config = require __DIR__ . '/dbconfig.php';

$db = new ClickHouseDB\Client($config);
$db->database('default');
$db->setConnectTimeOut(1);

$db->write('
    CREATE TABLE IF NOT EXISTS host_alive_status (
        event_date Date DEFAULT toDate(event_time),
        event_time DateTime,
        site_id Int32,
        alive Boolean
    )
    ENGINE = SummingMergeTree(event_date, (site_id, event_time, event_date), 8192)
');