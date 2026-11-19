# Development image for running Composer, tests and static analysis without a local PHP.
FROM php:8.5-cli

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/

RUN apt-get update && apt-get install -y --no-install-recommends git unzip && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions pcov zip

ENV COMPOSER_HOME=/tmp/composer

WORKDIR /app
