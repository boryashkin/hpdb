<?php

require '../vendor/autoload.php';

$container = require '../src/config/container.php';
$app = new Slim\App($container);

$app->group('', function () use ($app) {
    $app->get('/', \app\actions\web\Index::class);
    $app->get('/profile/{id}', \app\actions\web\Profile::class);
    $app->get('/article/create-website', \app\actions\web\Article::class);
})->add(\app\middlewares\WebMetricsMiddleware::class);
$app->group('', function () use ($app) {
    $app->get('/api/v1/profile/index', \app\actions\api\v1\profile\Index::class);
    $app->get('/api/v1/profile/index-light', \app\actions\api\v1\profile\Index::class);
    $app->post('/api/v1/profile/create', \app\actions\api\v1\profile\Create::class);
    $app->post('/api/v1/reaction', \app\actions\api\v1\Reaction::class);
    $app->get('/api/v1/reaction', \app\actions\api\v1\reaction\Index::class);
    $app->get('/api/v1/group', \app\actions\api\v1\group\Index::class);
    $app->post('/api/v1/group', \app\actions\api\v1\group\Create::class);
    $app->delete('/api/v1/group/{id}', \app\actions\api\v1\group\Delete::class);
    $app->patch('/api/v1/group/{id}', \app\actions\api\v1\group\Update::class);

    $app->put('/api/v1/rpc/add-website-to-group', \app\actions\api\v1\rpc\AddWebsiteToGroup::class);
    $app->put('/api/v1/rpc/parse-github-contributors', \app\actions\api\v1\rpc\ParseGithubContributiorsPage::class);
})->add(\app\middlewares\ApiMetricsMiddleware::class);
$app->group('', function () use ($app) {
    $app->get('/proxy/{id}/', \app\actions\proxy\Index::class);
    $app->get('/proxy/{id}/{path:.*}', \app\actions\proxy\Index::class);
})->add(\app\middlewares\ProxyMetricsMiddleware::class);

$app->get('/service/metrics', \app\actions\service\metrics\Index::class);

$app->run();
