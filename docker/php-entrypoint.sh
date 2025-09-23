#!/bin/sh

cron
chown -R www-data:www-data /var/www/html/logs
exec docker-php-entrypoint "$@"
