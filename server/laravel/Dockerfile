FROM php:8.3-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    libpng-dev libjpeg-turbo-dev libzip-dev libxml2-dev \
    unzip git mysql-client bash \
  && docker-php-ext-configure gd --with-jpeg \
  && docker-php-ext-install -j$(nproc) pdo_mysql gd zip xml

# Install Composer (copy from official composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files and install dependencies (production)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Copy the rest of the Laravel app
COPY . .

# Generate optimized autoload (if needed)
RUN composer dump-autoload --optimize --no-dev

# Set proper permissions (storage and cache)
RUN chown -R www-data:www-data storage bootstrap/cache \
  && chmod -R 775 storage bootstrap/cache

# Entrypoint: run migrations and then start php-fpm
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]