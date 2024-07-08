#!/bin/sh
set -e

echo "Running composer..."
composer --working-dir=/var/www/src install --optimize-autoloader --no-interaction --no-progress

echo "Starting supervisord..."
/usr/bin/supervisord --user nobody -c /etc/supervisor/conf.d/supervisord.conf
