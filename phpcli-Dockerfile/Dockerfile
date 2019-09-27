FROM php:7.3.2-cli
RUN apt-get update
RUN apt-get install -y git zip unzip libzip-dev zlib1g
RUN docker-php-ext-install zip
RUN php -r "copy('https://getcomposer.org/installer', '/tmp/composer-setup.php');"
RUN php /tmp/composer-setup.php
RUN mv composer.phar /usr/local/bin/composer
EXPOSE 9000