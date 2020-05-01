<?php

require '../vendor/autoload.php';

$container = require '../config/container.php';
$app = new Slim\App($container);

$app->group('', function () use ($app) {
    $app->get('/', \App\Web\Web\Actions\Index::class);
    $app->get('/profile/{id}', \App\Web\Web\Actions\Profile::class);
    $app->get('/article/create-website', \App\Web\Web\Actions\Article::class);
    $app->get('/crawler', \App\Web\Web\Actions\Crawler::class);
})->add(\App\Web\Web\Middlewares\WebMetricsMiddleware::class);
$app->group('', function () use ($app) {
    $app->get('/api/v1/profile/index', \App\Web\Api\V1\Profile\Actions\Index::class);
    $app->get('/api/v1/profile/index-light', \App\Web\Api\V1\Profile\Actions\Index::class);
    $app->post('/api/v1/profile/create', \App\Web\Api\V1\Profile\Actions\Create::class);
    $app->post('/api/v1/reaction', \App\Web\Api\V1\Reaction\Actions\Reaction::class);
    $app->get('/api/v1/reaction', \App\Web\Api\V1\Reaction\Actions\Index::class);
    $app->get('/api/v1/group', \App\Web\Api\V1\Group\Actions\Index::class);
    $app->post('/api/v1/group', \App\Web\Api\V1\Group\Actions\Create::class);
    $app->delete('/api/v1/group/{id}', \App\Web\Api\V1\Group\Actions\Delete::class);
    $app->patch('/api/v1/group/{id}', \App\Web\Api\V1\Group\Actions\Update::class);
    $app->get('/api/v1/feed', \App\Web\Api\V1\Feed\Actions\Index::class);

    $app->put('/api/v1/rpc/add-website-to-group', \App\Web\Api\V1\Rpc\Actions\AddWebsiteToGroup::class);
    $app->put('/api/v1/rpc/parse-github-contributors', \App\Web\Api\V1\Rpc\Actions\ParseGithubContributiorsPage::class);
})->add(\App\Web\Api\V1\Middlewares\ApiMetricsMiddleware::class);
$app->group('', function () use ($app) {
    $app->get('/proxy/{id}/', \App\Web\Proxy\Actions\Index::class);
    $app->get('/proxy/{id}/{path:.*}', \App\Web\Proxy\Actions\Index::class);
})->add(\App\Web\Proxy\Middlewares\ProxyMetricsMiddleware::class);
$app->group('/admin', function () use ($app) {
    $app->get('/website/categorisation/', \App\Web\Admin\Category\Actions\WebsiteCategorisation\Index::class);
    $app->map(['get', 'post'], '/website/categorisation/{id}', \App\Web\Admin\Category\Actions\WebsiteCategorisation\Update::class);
})
    ->add(\App\Web\Admin\Middlewares\AdminMetricsMiddleware::class)
    ->add(\App\Web\Admin\Middlewares\UserAssignHashMiddleware::class)
    ->add(\App\Web\Common\Middlewares\Security\IpPassFilterMiddleware::class);

$app->get('/service/metrics', \App\Web\Service\Metrics\Actions\Index::class);

$app->run();
