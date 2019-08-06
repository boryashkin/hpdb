<?php

require '../vendor/autoload.php';

$container = require '../src/config/container.php';
$app = new Slim\App($container);

$app->get('/', \app\actions\web\Index::class);
$app->get('/profile/{id}', \app\actions\web\Profile::class);



$app->get('/api/v1/profile/index', \app\actions\api\v1\profile\Index::class);
$app->post('/api/v1/reaction', \app\actions\api\v1\Reaction::class);
$app->get('/api/v1/reaction', \app\actions\api\v1\reaction\Index::class);
$app->get('/proxy/{id}/', \app\actions\proxy\Index::class);
$app->get('/proxy/{id}/{path:.*}', \app\actions\proxy\Index::class);

$app->get('/article/create-website', \app\actions\web\Article::class);

$app->run();
