<?php

require '../vendor/autoload.php';

$container = require '../config/container.php';
$app = new Slim\App($container);
$app->add(\App\Web\Api\V1\Middlewares\CorsMiddleware::class);
$app->group('', function () use ($app) {
    $app->get('/', \App\Web\Web\Actions\Index::class);
    $app->get('/profile/{id}', \App\Web\Web\Actions\Profile::class);
    $app->get('/article/create-website', \App\Web\Web\Actions\Article::class);
    $app->get('/crawler', \App\Web\Web\Actions\Crawler::class);
})->add(\App\Web\Web\Middlewares\WebMetricsMiddleware::class);
$app->group('/api/v1', function () use ($app) {
    $app->get('/docs.yml', \App\Web\Api\V1\Docs\Actions\Index::class);
    $app->get('/profile', \App\Web\Api\V1\Profile\Actions\Index::class);
    $app->get('/profile/index-light', \App\Web\Api\V1\Profile\Actions\Index::class);
    $app->post('/profile', \App\Web\Api\V1\Profile\Actions\Create::class);
    $app->post('/reaction', \App\Web\Api\V1\Reaction\Actions\Create::class);
    $app->get('/group', \App\Web\Api\V1\Group\Actions\Index::class);
    $app->post('/group', \App\Web\Api\V1\Group\Actions\Create::class);
    $app->delete('/group/{id}', \App\Web\Api\V1\Group\Actions\Delete::class);
    $app->get('/group/{slug}', \App\Web\Api\V1\Group\Actions\View::class);
    $app->patch('/group/{id}', \App\Web\Api\V1\Group\Actions\Update::class);
    $app->get('/feed', \App\Web\Api\V1\Feed\Actions\Index::class);
    $app->post('/user', \App\Web\Api\V1\User\Actions\Create::class);

    $app->group('/rpc', function () use ($app) {
        $app->put('/add-website-to-group', \App\Web\Api\V1\Rpc\Actions\AddWebsiteToGroup::class);
        $app->put('/parse-github-contributors', \App\Web\Api\V1\Rpc\Actions\ParseGithubContributiorsPage::class);
        $app->put('/auth', \App\Web\Api\V1\Rpc\Actions\Auth::class);
    });

    $app->group('/rpc', function () use ($app) {
        $app->get('/current-user', \App\Web\Api\V1\User\Actions\CurrentUser::class);
        $app->get('/my-groups', \App\Web\Api\V1\Group\Actions\MyGroups::class);
    })
        ->add(\App\Web\Api\V1\Middlewares\AuthRequiredMiddleware::class);
})
    ->add(\App\Web\Api\V1\Middlewares\DbQueryMetricsMiddleware::class)
    ->add(\App\Web\Api\V1\Middlewares\AuthenticationMiddleware::class)
    ->add(\App\Web\Api\V1\Middlewares\ApiMetricsMiddleware::class);
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
