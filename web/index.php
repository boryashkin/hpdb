<?php

require '../vendor/autoload.php';

$container = require '../config/container.php';
$app = new Slim\App($container);

$app->group('', function () use ($app) {
    $app->get('/', \App\Web\Actions\Web\Index::class);
    $app->get('/profile/{id}', \App\Web\Actions\Web\Profile::class);
    $app->get('/article/create-website', \App\Web\Actions\Web\Article::class);
    $app->get('/crawler', \App\Web\Actions\Web\Crawler::class);
})->add(\App\Web\Middlewares\WebMetricsMiddleware::class);
$app->group('', function () use ($app) {
    $app->get('/api/v1/profile/index', \App\Web\Actions\Api\V1\Profile\Index::class);
    $app->get('/api/v1/profile/index-light', \App\Web\Actions\Api\V1\Profile\Index::class);
    $app->post('/api/v1/profile/create', \App\Web\Actions\Api\V1\Profile\Create::class);
    $app->post('/api/v1/reaction', \App\Web\Actions\Api\V1\Reaction::class);
    $app->get('/api/v1/reaction', \App\Web\Actions\Api\V1\Reaction\Index::class);
    $app->get('/api/v1/group', \App\Web\Actions\Api\V1\Group\Index::class);
    $app->post('/api/v1/group', \App\Web\Actions\Api\V1\Group\Create::class);
    $app->delete('/api/v1/group/{id}', \App\Web\Actions\Api\V1\Group\Delete::class);
    $app->patch('/api/v1/group/{id}', \App\Web\Actions\Api\V1\Group\Update::class);
    $app->get('/api/v1/feed', \App\Web\Actions\Api\V1\Feed\Index::class);

    $app->put('/api/v1/rpc/add-website-to-group', \App\Web\Actions\Api\V1\Rpc\AddWebsiteToGroup::class);
    $app->put('/api/v1/rpc/parse-github-contributors', \App\Web\Actions\Api\V1\Rpc\ParseGithubContributiorsPage::class);
})->add(\App\Web\Middlewares\ApiMetricsMiddleware::class);
$app->group('', function () use ($app) {
    $app->get('/proxy/{id}/', \App\Web\Actions\Proxy\Index::class);
    $app->get('/proxy/{id}/{path:.*}', \App\Web\Actions\Proxy\Index::class);
})->add(\App\Web\Middlewares\ProxyMetricsMiddleware::class);
$app->group('/admin', function () use ($app) {
    $app->get('/website/categorisation/', \App\Web\Actions\Admin\Website\Categorisation\Index::class);
    $app->map(['get', 'post'], '/website/categorisation/{id}', \App\Web\Actions\Admin\Website\Categorisation\Update::class);
})
    ->add(\App\Web\Middlewares\AdminMetricsMiddleware::class)
    ->add(\App\Web\Middlewares\UserAssignHashMiddleware::class)
    ->add(\App\Web\Middlewares\Security\IpPassFilterMiddleware::class);

$app->get('/service/metrics', \App\Web\Actions\Service\Metrics\Index::class);

$app->run();
