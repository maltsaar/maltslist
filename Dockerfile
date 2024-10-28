FROM alpine:latest

# Install packages
RUN apk add --no-cache \
    curl \
    nginx \
    php83 \
    php83-ctype \
    php83-curl \
    php83-dom \
    php83-fileinfo \
    php83-fpm \
    php83-gd \
    php83-intl \
    php83-mbstring \
    php83-mysqli \
    php83-opcache \
    php83-openssl \
    php83-phar \
    php83-session \
    php83-tokenizer \
    php83-xml \
    php83-xmlreader \
    php83-xmlwriter \
    sqlite \
    supervisor

# Configure PHP-FPM
ENV PHP_INI_DIR /etc/php83
COPY config/fpm-pool.conf ${PHP_INI_DIR}/php-fpm.d/www.conf
COPY config/php.ini ${PHP_INI_DIR}/conf.d/custom.ini

# Install maltslist specific dependencies
RUN apk add php83-sqlite3

# Copy nginx configuration file
COPY config/nginx.conf /etc/nginx/nginx.conf

# Copy nginx configuration for maltslist
COPY config/nginx-maltslist.conf /etc/nginx/conf.d/default.conf

RUN chown -R nobody:nobody /run /var/lib/nginx /var/log/nginx

# Configure supervisord
COPY config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Install composer from the official image
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Add nobody to the tty group so he write to /dev/stdout
RUN addgroup nobody tty

COPY ./entrypoint.sh /usr/bin/
ENTRYPOINT [ "entrypoint.sh" ]
