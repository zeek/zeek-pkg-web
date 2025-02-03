#!/bin/sh

cron
exec docker-php-entrypoint "$@"
