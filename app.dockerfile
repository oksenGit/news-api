FROM php:8.4-fpm-alpine

WORKDIR /var/www/

# Install minimal alpine packages
RUN apk add --no-cache --update \
    libzip-dev \
    libxml2-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Install php extensions
RUN docker-php-ext-install \
    pdo_mysql \
    zip \
    xml \
    gd \
    exif

RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www

# Install composer dependencies
COPY --chown=www-data:www-data .env /var/www/.env

# Install composer and install packages
COPY --chown=www-data:www-data ./composer.lock ./composer.json /var/www/
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-scripts --no-autoloader

# Copy existing application directory contents
COPY --chown=www-data:www-data . .

USER www-data
