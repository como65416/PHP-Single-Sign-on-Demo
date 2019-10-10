FROM php:7.3.2-fpm-alpine
RUN apk add git zip unzip libzip-dev composer
RUN docker-php-ext-install zip
EXPOSE 9000