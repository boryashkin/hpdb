<?php

require '../vendor/autoload.php';

$container = require '../src/config/container.php';
$app = new Slim\App($container);

$app->get('/', \app\actions\web\Index::class);
$app->get('/profile/{id}', \app\actions\web\Profile::class);



$app->get('/api/v1/index', \app\actions\api\v1\Index::class);
$app->post('/api/v1/reaction', \app\actions\api\v1\Reaction::class);
$app->get('/proxy/{id}/', \app\actions\proxy\Index::class);
$app->get('/proxy/{id}/{path:.*}', \app\actions\proxy\Index::class);

$app->run();
