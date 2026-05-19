#!/bin/bash
set -e

APP_DIR=/var/www/api

echo "Running as UID: $(id -u), GID: $(id -g)"

if [ ! -f "$APP_DIR/.env" ]; then
  echo "Creating .env from example..."
  if [ -f "$APP_DIR/.env.example" ]; then
    cp "$APP_DIR/.env.example" "$APP_DIR/.env"
  fi
fi

mkdir -p $APP_DIR/storage \
         $APP_DIR/storage/framework/cache \
         $APP_DIR/storage/framework/sessions \
         $APP_DIR/storage/framework/views \
         $APP_DIR/storage/logs

chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache || true

exec php-fpm