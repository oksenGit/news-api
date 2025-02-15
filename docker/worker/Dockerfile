FROM php:8.4-cli-alpine

WORKDIR /var/www/

# Install minimal alpine packages
RUN apk add --no-cache --update \
    libzip-dev \
    libxml2-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    supervisor

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

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set up supervisor configuration
COPY docker/worker/supervisor.conf /etc/supervisor/conf.d/supervisor.conf

# Copy existing application directory contents
COPY --chown=www-data:www-data . .

USER www-data

# Start supervisor
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisor.conf"] 