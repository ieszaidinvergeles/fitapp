FROM php:8.2-fpm-alpine

# ─── SYSTEM DEPENDENCIES ──────────────────────────────────────────────────────
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    bash \
    mysql-client \
    oniguruma-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        xml \
        opcache

# ─── REDIS EXTENSION ────────────────────────────────────────────────────────
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis-6.0.2 \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# ─── COMPOSER ───────────────────────────────────────────────────────────────
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# ─── WORKING DIRECTORY ───────────────────────────────────────────────────────
WORKDIR /var/www/laravel

# ─── APPLICATION CODE ─────────────────────────────────────────────────────────
# Code is now mounted from the host.
# We copy an entrypoint that ensures dependencies and DB migrations are complete.
RUN chown -R www-data:www-data /var/www/laravel

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER www-data

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
