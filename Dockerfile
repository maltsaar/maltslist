FROM trafex/php-nginx:latest

COPY config/nginx.conf /etc/nginx/conf.d/default.conf
COPY --chown=nobody:nobody src /var/www/src

USER root
RUN apk add php82-sqlite3

USER nobody
WORKDIR /var/www/src

# Install composer from the official image
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Run composer install to install the dependencies
RUN composer install --optimize-autoloader --no-interaction --no-progress
