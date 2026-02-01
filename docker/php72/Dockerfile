FROM php:7.2-cli

RUN set -eux; \
    sed -i 's|deb.debian.org/debian|archive.debian.org/debian|g' /etc/apt/sources.list; \
    sed -i 's|security.debian.org/debian-security|archive.debian.org/debian-security|g' /etc/apt/sources.list; \
    sed -i '/buster-updates/d' /etc/apt/sources.list; \
    echo 'Acquire::Check-Valid-Until "false";' > /etc/apt/apt.conf.d/99no-check-valid-until; \
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
