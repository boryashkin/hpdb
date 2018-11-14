<?php
define('ENV_PROD', true);

require '../vendor/autoload.php';

$container = new \Slim\Container([
    'settings' => [
        'displayErrorDetails' => !ENV_PROD,
    ],
]);
$app = new Slim\App($container);

$app->get('/', \app\actions\web\Index::class);

$app->run();
