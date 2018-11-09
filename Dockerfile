FROM php:7.2-fpm-alpine

RUN apk add --update \
        postgresql-dev \
        libmcrypt-dev \
        zlib-dev \
        openssl \
        unzip \
        curl \
        git

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-install pdo mbstring pgsql pdo_pgsql zip && \
        docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql

WORKDIR /var/www
COPY . /var/www
COPY .env.example .env
RUN composer install
EXPOSE 9000
CMD 'php -S 0.0.0.0:$PORT public/'
