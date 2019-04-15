FROM php:7.3.3-fpm-alpine3.8

RUN printf "\n%s\n%s" "@edge http://dl-cdn.alpinelinux.org/alpine/edge/main" "@testing http://dl-cdn.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories \
    && apk --update upgrade \
    && apk add \
    autoconf automake make gcc g++ libtool pkgconfig libmcrypt-dev \
    re2c libressl@edge libressl-dev@edge git zlib-dev xdg-utils \
    openssh-client libxslt-dev ca-certificates \
    gmp-dev postgresql-dev libzip-dev libxml2-dev libintl icu icu-dev \
    && update-ca-certificates

RUN docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        bcmath \
        zip \
        json \
        iconv \
        fileinfo \
        dom \
        pcntl \
        pdo \
        sockets \
        gmp \
        mbstring \
        tokenizer \
        xml \
        intl

RUN apk add rabbitmq-c@testing rabbitmq-c-dev@testing \
    && pecl install amqp \
    && docker-php-ext-enable amqp

RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

RUN pecl install apcu \
    && docker-php-ext-enable apcu

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN pecl install redis \
    && docker-php-ext-enable redis

RUN curl -sS "https://getcomposer.org/installer" | php
RUN mv composer.phar /usr/local/bin/composer

RUN rm -rf /var/cache/apk && mkdir -p /var/cache/apk

WORKDIR /var/www
CMD ["php-fpm"]