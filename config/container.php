<?php

const CONTAINER_CONFIG_SETTINGS = 'settings';
const CONTAINER_CONFIG_LOGGER = 'logger';
const CONTAINER_CONFIG_VIEW = 'view';
const CONTAINER_CONFIG_MONGO = 'mongodb';
const CONTAINER_CONFIG_ELASTIC = 'elastic';
const CONTAINER_CONFIG_METRICS = 'metrics';
const CONTAINER_CONFIG_REDIS_CACHE = 'redisCache';
const CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_CRAWLERS = 'redisStreamConnectionCrawlers';
const CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_DISCOVERERS = 'redisStreamConnectionDiscoveres';
const CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_PERSISTORS = 'redisStreamConnectionPersistors';
const CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_PROCESSORS = 'redisStreamConnectionProcessor';
const CONTAINER_CONFIG_REDIS_STREAM_SERIALIZER = 'redisStreamSerializer';
const CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_CRAWLERS = 'redisStreamTransportCrawlers';
const CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_DISCOVERERS = 'redisStreamTransportDiscoverers';
const CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_PERSISTORS = 'redisStreamTransportPersistors';
const CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_PROCESSORS = 'redisStreamTransportProcessors';
const CONTAINER_CONFIG_REDIS_STREAM_DISCOVERERS = 'redisStreamDiscoverers';
const CONTAINER_CONFIG_REDIS_STREAM_CRAWLERS = 'redisStreamCrawlers';
const CONTAINER_CONFIG_REDIS_STREAM_PERSISTORS = 'redisStreamPersistors';
const CONTAINER_CONFIG_REDIS_STREAM_PROCESSORS = 'redisStreamProcessors';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/../.env');
define('ENV_PROD', \getenv('ENV_PROD', true) === 'true');

\Illuminate\Database\Eloquent\Model::setConnectionResolver(new \Illuminate\Database\ConnectionResolver());

$messageBus = require 'message-bus.array.php';

return new \Slim\Container(array_merge([
    CONTAINER_CONFIG_SETTINGS => [
        'displayErrorDetails' => !ENV_PROD,
        'routerCacheFile' => __DIR__ . '/../data/slim/routes.cache.php',
    ],
    CONTAINER_CONFIG_LOGGER => function (Slim\Container $c) {
        return new \App\Common\Services\StdLogger($enableDebug = !ENV_PROD);
    },
    CONTAINER_CONFIG_VIEW => function (Slim\Container $c) {
        $view = new \Slim\Views\Twig(__DIR__ . '/../Src/Web/views', [
            'cache' => __DIR__ . '/../data/twig/cache',
        ]);

        // Instantiate and add Slim specific extension
        $router = $c->get('router');
        $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
        $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

        return $view;
    },
    Illuminate\Database\Capsule\Manager::class => function (Slim\Container $c) {
        return new \Illuminate\Database\Capsule\Manager();
    },
    SQLite3::class => function (Slim\Container $c) {
        $c->get(\Illuminate\Database\Capsule\Manager::class)->addConnection([
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../domainslibrary.db',
            //'charset'   => 'utf8',
            //'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ], 'sqlite');
        $manager = $c->get(\Illuminate\Database\Capsule\Manager::class);
        \Illuminate\Database\Eloquent\Model::getConnectionResolver()
            ->addConnection('sqlite', $manager->getConnection('sqlite'));

        return $manager->getConnection('sqlite');
    },
    CONTAINER_CONFIG_MONGO => function (Slim\Container $c) {
        /** @var \Illuminate\Database\Capsule\Manager $manager */
        $manager = $c->get(\Illuminate\Database\Capsule\Manager::class);
        $manager->getDatabaseManager()->extend('mongodb', function ($config, $name) {
            $config['name'] = $name;

            return new Jenssegers\Mongodb\Connection($config);
        });
        $manager->addConnection([
            'driver' => 'mongodb',
            'host' => \getenv('MONGO_HOST', true),
            'port' => \getenv('MONGO_PORT', true),
            'database' => \getenv('MONGO_DATABASE', true),
            'username' => \getenv('MONGO_USERNAME', true),
            'password' => \getenv('MONGO_PASSWORD', true),
            'options' => [
                'database' => 'admin', // sets the authentication database required by mongo 3
            ],
        ], 'mongodb');
        \Illuminate\Database\Eloquent\Model::getConnectionResolver()
            ->addConnection('mongodb', $manager->getConnection('mongodb'));

        return $manager->getConnection('mongodb');
    },
    CONTAINER_CONFIG_REDIS_CACHE => function (Slim\Container $c) {
        $config = [
            'schema' => 'tcp',
            'host' => \getenv('REDIS_HOST', true),
            'port' => 6379,
        ];
        $connection = new Predis\Client($config);

        return new Symfony\Component\Cache\Adapter\RedisAdapter($connection);
    },
    CONTAINER_CONFIG_ELASTIC => function (Slim\Container $c) {
        return \Elasticsearch\ClientBuilder::create()->setHosts([\getenv('ELASTIC_HOST')])->build();
    },
    CONTAINER_CONFIG_METRICS => function (Slim\Container $c) {
        return new \App\Common\Services\MetricsCollector(
            new \Prometheus\Storage\Redis([
                'host' => \getenv('REDIS_HOST', true),
                'port' => 6379,
            ])
        );
    },
    \App\Common\Services\Scheduled\Base64Serializer::class => function (Slim\Container $c) {
        return new \App\Common\Services\Scheduled\Base64Serializer();
    },
    \App\Common\Services\IpCheckService::class => static function (Slim\Container $c) {
        $adminAllowedIp = \getenv('SECURITY_ADMIN_ALLOWED_IP', true) ?: '172.172.172.172';

        return new \App\Common\Services\IpCheckService($adminAllowedIp);
    },
], $messageBus));
