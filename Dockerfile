###############################################
# Stage 1
###############################################

FROM node:20-alpine AS frontend

WORKDIR /build

COPY package*.json ./

RUN npm ci

COPY . .

RUN npm run build


###############################################
# Stage 2
###############################################

FROM php:8.4-fpm-alpine AS vendor

WORKDIR /build

RUN apk add --no-cache \
    git \
    unzip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

COPY . .

RUN composer dump-autoload \
    --classmap-authoritative \
    --no-dev


###############################################
# Stage 3
###############################################

FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
        pdo_mysql \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        zip \
        redis

RUN apk add --no-cache dos2unix

COPY --from=vendor /build /var/www/html

COPY --from=frontend /build/public/build /var/www/html/public/build

COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN dos2unix /usr/local/bin/entrypoint.sh \
 && chmod +x /usr/local/bin/entrypoint.sh

RUN mkdir -p \
    storage \
    bootstrap/cache

RUN chown -R www-data:www-data /var/www/html \
 && chmod -R ug+rwx storage bootstrap/cache

USER www-data

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["php-fpm"]
