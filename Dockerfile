FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpq-dev \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_pgsql pdo_mysql intl zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

ENV APP_ENV=dev \
    COMPOSER_ALLOW_SUPERUSER=1 \
    PATH="/app/bin:${PATH}"

EXPOSE 8000

CMD ["bash", "-lc", "if [ ! -f vendor/autoload.php ]; then composer install; fi && php -S 0.0.0.0:8000 -t public"]
