# Override these with registry digests for byte-for-byte base image reproduction.
ARG NODE_IMAGE=node:24-bookworm-slim
ARG COMPOSER_IMAGE=composer:2
ARG PHP_IMAGE=php:8.4-apache-bookworm

FROM ${NODE_IMAGE} AS frontend
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

FROM ${COMPOSER_IMAGE} AS composer

FROM ${PHP_IMAGE}
RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" bcmath intl pdo_mysql zip \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
    && mkdir -p storage/app/private storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x deploy/entrypoint.sh
COPY --from=frontend /build/public/build ./public/build
COPY deploy/apache.conf /etc/apache2/sites-available/000-default.conf
COPY deploy/php.ini /usr/local/etc/php/conf.d/99-lioloto.ini
ENTRYPOINT ["/var/www/html/deploy/entrypoint.sh"]
CMD ["apache2-foreground"]
