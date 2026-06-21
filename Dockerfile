# --- Frontend Assets Builder ---
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY tailwind.config.js* vite.config.js* postcss.config.js* ./
COPY resources/ ./resources/
COPY public/ ./public/
RUN npm run build

# --- Composer Dependencies Builder ---
FROM php:8.4-fpm-alpine AS composer-builder
WORKDIR /app
RUN apk add --no-cache git unzip
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer*.json ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# --- Final Runtime Stage ---
FROM php:8.4-fpm-alpine
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    git \
    unzip \
    dos2unix

# Install PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Copy configurations
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/fpm-pool.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Copy composer binary from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Copy built vendors and frontend assets
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=node-builder /app/public/build ./public/build

# Run final Composer dump-autoload and optimize
RUN composer dump-autoload --no-dev --classmap-authoritative

# Ensure entrypoint is executable and has correct line endings (to prevent Windows CRLF issues)
RUN dos2unix /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

# Setup directory permissions
RUN chown -R www-data:www-data /var/www/html \
    && mkdir -p /var/run /var/log/nginx /var/log/supervisor /var/cache/nginx \
    && chown -R www-data:www-data /var/run /var/log /var/cache/nginx

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
