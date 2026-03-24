FROM alpine:latest

# Install packages
RUN apk add --no-cache \
    curl \
    nginx \
    php85 \
    php85-ctype \
    php85-curl \
    php85-dom \
    php85-fileinfo \
    php85-fpm \
    php85-gd \
    php85-intl \
    php85-mbstring \
    php85-openssl \
    php85-phar \
    php85-session \
    php85-tokenizer \
    php85-xml \
    php85-xmlreader \
    php85-xmlwriter \
    sqlite \
    supervisor

# Configure PHP-FPM
RUN ln -s /usr/bin/php85 /usr/bin/php
ENV PHP_INI_DIR /etc/php85
COPY config/fpm-pool.conf ${PHP_INI_DIR}/php-fpm.d/www.conf
COPY config/php.ini ${PHP_INI_DIR}/conf.d/custom.ini

# Install maltslist specific dependencies
RUN apk add php85-sqlite3

# Copy nginx configuration file
COPY config/nginx.conf /etc/nginx/nginx.conf

# Copy nginx configuration for maltslist
COPY config/nginx-maltslist.conf /etc/nginx/conf.d/default.conf

RUN chown -R nobody:nobody /run /var/lib/nginx /var/log/nginx

# Configure supervisord
COPY config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Install composer from the official image
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Add nobody to the tty group so we can write to /dev/stdout
RUN addgroup nobody tty

COPY ./entrypoint.sh /usr/bin/
ENTRYPOINT [ "entrypoint.sh" ]
