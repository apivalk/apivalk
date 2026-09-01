FROM php:7.4-cli

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
      git \
      unzip \
      zip \
      libonig-dev \
    ; \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mbstring

RUN git config --global --add safe.directory /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
