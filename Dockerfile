# syntax=docker/dockerfile:1

# PHP with Composer: the base of the build stages and the runtime.
FROM php:8.5-cli AS php-base
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app

# Production PHP dependencies and the application code.
FROM php-base AS app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --no-dev --no-interaction

# Frontend build: the Wayfinder plugin calls `php artisan`, so it needs PHP and vendor.
FROM app AS frontend
COPY --from=node:22-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm
RUN npm ci --no-audit --no-fund
RUN npm run build

# Runtime: production vendor, code and built assets, without Composer and Node.
FROM php:8.5-cli AS runtime
WORKDIR /app
COPY --from=app /app /app
COPY --from=frontend /app/public/build /app/public/build
RUN chown -R www-data:www-data database storage bootstrap/cache
USER www-data

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PORT=8080
EXPOSE 8080

ENTRYPOINT ["sh", "/app/docker-entrypoint.sh"]
