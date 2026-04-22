FROM wordpress:php8.2-fpm-alpine

# ─── Extra PHP extensions ─────────────────────────────────────────────────────
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# ─── WP-CLI ───────────────────────────────────────────────────────────────────
RUN curl -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp

WORKDIR /var/www/wordpress

EXPOSE 9000
CMD ["php-fpm"]
