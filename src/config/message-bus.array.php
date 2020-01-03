<?php

return [
    CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_CRAWLERS => function (\Slim\Container $c) {
        $host = \getenv('REDIS_HOST', true);
        $port = 6379;
        $dsn = "redis://$host:$port";
        $options = [
            'stream' => 'crawlers',
            'group' => 'default',
            'consumer' => \getenv('REDIS_QUEUE_CONSUMER'),
        ];
        return \Symfony\Component\Messenger\Transport\RedisExt\Connection::fromDsn($dsn, $options);
    },
    CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_DISCOVERERS => function (\Slim\Container $c) {
        $host = \getenv('REDIS_HOST', true);
        $port = 6379;
        $dsn = "redis://$host:$port";
        $options = [
            'stream' => 'discoverers',
            'group' => 'default',
            'consumer' => \getenv('REDIS_QUEUE_CONSUMER'),
        ];
        return \Symfony\Component\Messenger\Transport\RedisExt\Connection::fromDsn($dsn, $options);
    },
    CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_PERSISTORS => function (\Slim\Container $c) {
        $host = \getenv('REDIS_HOST', true);
        $port = 6379;
        $dsn = "redis://$host:$port";
        $options = [
            'stream' => 'persistors',
            'group' => 'default',
            'consumer' => \getenv('REDIS_QUEUE_CONSUMER'),
        ];
        return \Symfony\Component\Messenger\Transport\RedisExt\Connection::fromDsn($dsn, $options);
    },
    CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_PROCESSORS => function (\Slim\Container $c) {
        $host = \getenv('REDIS_HOST', true);
        $port = 6379;
        $dsn = "redis://$host:$port";
        $options = [
            'stream' => 'processors',
            'group' => 'default',
            'consumer' => \getenv('REDIS_QUEUE_CONSUMER'),
        ];
        return \Symfony\Component\Messenger\Transport\RedisExt\Connection::fromDsn($dsn, $options);
    },
    CONTAINER_CONFIG_REDIS_STREAM_SERIALIZER => function (\Slim\Container $c) {

        return new \Symfony\Component\Messenger\Transport\Serialization\PhpSerializer();
    },
    CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_CRAWLERS => function (\Slim\Container $c) {

        return new \Symfony\Component\Messenger\Transport\RedisExt\RedisTransport(
            $c->get(CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_CRAWLERS),
            $c->get(CONTAINER_CONFIG_REDIS_STREAM_SERIALIZER)
        );
    },
    CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_DISCOVERERS => function (\Slim\Container $c) {

        return new \Symfony\Component\Messenger\Transport\RedisExt\RedisTransport(
            $c->get(CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_DISCOVERERS),
            $c->get(CONTAINER_CONFIG_REDIS_STREAM_SERIALIZER)
        );
    },
    CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_PERSISTORS => function (\Slim\Container $c) {

        return new \Symfony\Component\Messenger\Transport\RedisExt\RedisTransport(
            $c->get(CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_PERSISTORS),
            $c->get(CONTAINER_CONFIG_REDIS_STREAM_SERIALIZER)
        );
    },
    CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_PROCESSORS => function (\Slim\Container $c) {

        return new \Symfony\Component\Messenger\Transport\RedisExt\RedisTransport(
            $c->get(CONTAINER_CONFIG_REDIS_STREAM_CONNECTION_PROCESSORS),
            $c->get(CONTAINER_CONFIG_REDIS_STREAM_SERIALIZER)
        );
    },
    CONTAINER_CONFIG_REDIS_STREAM_CRAWLERS => function (\Slim\Container $c) {
        $factory = new \app\messageBus\factories\MessageBusFactory($c);
        $factory->addSender('*', CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_CRAWLERS);

        return $factory->buildMessageBus();
    },
    CONTAINER_CONFIG_REDIS_STREAM_DISCOVERERS => function (\Slim\Container $c) {
        $factory = new \app\messageBus\factories\MessageBusFactory($c);
        $factory->addSender('*', CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_DISCOVERERS);

        return $factory->buildMessageBus();
    },
    CONTAINER_CONFIG_REDIS_STREAM_PERSISTORS => function (\Slim\Container $c) {
        $factory = new \app\messageBus\factories\MessageBusFactory($c);
        $factory->addSender('*', CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_PERSISTORS);

        return $factory->buildMessageBus();
    },
    CONTAINER_CONFIG_REDIS_STREAM_PROCESSORS => function (\Slim\Container $c) {
        $factory = new \app\messageBus\factories\MessageBusFactory($c);
        $factory->addSender('*', CONTAINER_CONFIG_REDIS_STREAM_TRANSPORT_PROCESSORS);

        return $factory->buildMessageBus();
    },
];
