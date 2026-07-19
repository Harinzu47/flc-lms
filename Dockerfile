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

RUN apk add --no-cache \
        icu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        libxml2-dev \
        dos2unix \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

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
