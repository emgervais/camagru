FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo pdo_mysql gd

WORKDIR /var/www