# Use oficial PHP with fpm.
FROM php:7.2.10-fpm

RUN apt-get update && apt-get -y install \
    git \
    zlib1g-dev \
    zip

# Install PHP extensions
RUN docker-php-ext-install \
    sockets \
    zip \
    bcmath

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer \
    && apt-get clean

# Copy source code and the docker environment
COPY ./php-producer/ /opt/app
COPY ./.env /opt/.env

# Set working directory
WORKDIR /opt/app/

# Create log & cache directory
RUN mkdir -p var/cache && mkdir -p var/log

# Set permissions
RUN usermod -u 1000 www-data
RUN chown -R www-data: \
    var/cache \
    var/log \
    /usr/local/etc/php/conf.d/ \
    /var/www

USER www-data
