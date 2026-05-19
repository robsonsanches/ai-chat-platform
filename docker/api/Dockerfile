FROM php:8.3-fpm

ENV DEBIAN_FRONTEND=noninteractive

ARG HOST_UID=1000
ARG HOST_GID=1000

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev libxml2-dev libonig-dev \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev libmagickwand-dev \
    libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install intl zip bcmath mysqli pdo_mysql mbstring xml gd curl \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# cria appuser com UID/GID do host
RUN groupadd -g ${HOST_GID} appuser \
    && useradd -u ${HOST_UID} -g appuser -m appuser

# altera PHP-FPM para rodar com appuser
RUN sed -i 's/^user = .*/user = appuser/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^group = .*/group = appuser/' /usr/local/etc/php-fpm.d/www.conf

# entrypoint
COPY docker/api/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/api

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

EXPOSE 9000