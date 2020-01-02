<?php

define('ENV_PROD', false);

const CONTAINER_CONFIG_SETTINGS = 'settings';
const CONTAINER_CONFIG_VIEW = 'view';
const CONTAINER_CONFIG_MONGO = 'mongodb';
const CONTAINER_CONFIG_REDIS_CACHE = 'redisCache';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__.'/../../.env');

\Illuminate\Database\Eloquent\Model::setConnectionResolver(new \Illuminate\Database\ConnectionResolver());

return new \Slim\Container([
    CONTAINER_CONFIG_SETTINGS => [
        'displayErrorDetails' => !ENV_PROD,
    ],
    CONTAINER_CONFIG_VIEW => function (\Slim\Container $c) {
        $view = new \Slim\Views\Twig(__DIR__ . '/../../src/views', []);

        // Instantiate and add Slim specific extension
        $router = $c->get('router');
        $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
        $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

        return $view;
    },
    Illuminate\Database\Capsule\Manager::class => function (\Slim\Container $c) {
        return new \Illuminate\Database\Capsule\Manager();
    },
    SQLite3::class => function (\Slim\Container $c) {
        $c->get(\Illuminate\Database\Capsule\Manager::class)->addConnection([
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../../domainslibrary.db',
            //'charset'   => 'utf8',
            //'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ], 'sqlite');
        $manager = $c->get(\Illuminate\Database\Capsule\Manager::class);
        \Illuminate\Database\Eloquent\Model::getConnectionResolver()
            ->addConnection('sqlite', $manager->getConnection('sqlite'));

        return $manager->getConnection('sqlite');
    },
    CONTAINER_CONFIG_MONGO => function (\Slim\Container $c) {
        /** @var \Illuminate\Database\Capsule\Manager $manager */
        $manager = $c->get(\Illuminate\Database\Capsule\Manager::class);
        $manager->getDatabaseManager()->extend('mongodb', function ($config, $name) {
            $config['name'] = $name;

            return new Jenssegers\Mongodb\Connection($config);
        });
        $manager->addConnection([
            'driver'   => 'mongodb',
            'host'     => \getenv('MONGO_HOST', true),
            'port'     => \getenv('MONGO_PORT', true),
            'database' => \getenv('MONGO_DATABASE', true),
            'username' => \getenv('MONGO_USERNAME', true),
            'password' => \getenv('MONGO_PASSWORD', true),
            'options'  => [
                'database' => 'admin' // sets the authentication database required by mongo 3
            ]
        ], 'mongodb');
        \Illuminate\Database\Eloquent\Model::getConnectionResolver()
            ->addConnection('mongodb', $manager->getConnection('mongodb'));

        return $manager->getConnection('mongodb');
    },
    CONTAINER_CONFIG_REDIS_CACHE => function (\Slim\Container $c) {
        $config = [
            'schema' => 'tcp',
            'host' => \getenv('REDIS_HOST', true),
            'port' => 6379,
        ];
        $connection = new Predis\Client($config);
        return new Symfony\Component\Cache\Adapter\RedisAdapter($connection);
    }
]);


