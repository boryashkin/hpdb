<?php
define('ENV_PROD', true);

require '../vendor/autoload.php';

$container = new \Slim\Container([
    'settings' => [
        'displayErrorDetails' => !ENV_PROD,
    ],
]);
$app = new Slim\App($container);

$app->get('/api/index/index', \app\actions\api\index\Index::class);

$app->run();
