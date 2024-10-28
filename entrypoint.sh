#!/bin/sh
set -e

echo "Running composer..."
composer --working-dir=/var/www/src install --optimize-autoloader --no-interaction --no-progress

echo "Creating database if it doesn't already exist..."
DB_PATH="/var/www/src/db/${DATABASE}"
if [ -f $DB_PATH ]
then
    echo "Database file exists"
else
    echo "Creating new database from schema because file doesn't exist..."
    su nobody -s /bin/sh -c "sqlite3 ${DB_PATH} < /var/www/src/schema.sql"
fi

echo "Starting supervisord..."
/usr/bin/supervisord --user nobody -c /etc/supervisor/conf.d/supervisord.conf
