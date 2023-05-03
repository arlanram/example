FROM php:8.2-cli-alpine3.17

WORKDIR /var/www

RUN apk add --no-cache ${PHPIZE_DEPS} libstdc++ curl-dev shadow

RUN docker-php-ext-install pcntl curl

RUN pecl install swoole redis && docker-php-ext-enable swoole redis

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apk del ${PHPIZE_DEPS}

RUN docker-php-source delete && rm -rf /tmp/* && rm -rf /var/cache/apk/*

RUN groupmod -o -g 1000 www-data && usermod -o -u 1000 -g www-data www-data && chown -R www-data:www-data /var/www

USER www-data