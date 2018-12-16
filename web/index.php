<?php
define('ENV_PROD', true);

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
    }
]);
$app = new Slim\App($container);

$app->get('/', \app\actions\web\Index::class);
$app->get('/profile/{id}', \app\actions\web\Profile::class);



$app->get('/api/v1/index', \app\actions\api\v1\Index::class);

$app->run();
