<?php
define('ENV_PROD', false);

require '../vendor/autoload.php';

$container = new \Slim\Container([
    'settings' => [
        'displayErrorDetails' => !ENV_PROD,
    ],
    'view' => function ($c) {
        $view = new \Slim\Views\Twig(__DIR__ . '/../src/views', []);

        // Instantiate and add Slim specific extension
        $router = $c->get('router');
        $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
        $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

        return $view;
    },
    'mongodb' => function (\Slim\Container $c) {
        return new Jenssegers\Mongodb\Eloquent\Builder();
    }
]);
$app = new Slim\App($container);

$app->get('/', \app\actions\web\Index::class);
$app->get('/profile/{id}', \app\actions\web\Profile::class);



$app->get('/api/v1/index', \app\actions\api\v1\Index::class);
$app->get('/proxy/{id}/', \app\actions\proxy\Index::class);
$app->get('/proxy/{id}/{path:.*}', \app\actions\proxy\Index::class);

$app->run();
