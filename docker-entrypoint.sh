#!/bin/sh
# Prepares the SQLite database and starts the app on $PORT.
set -e

# Hexlet's check runs the image with PORT only: generate a throwaway key so
# the app still boots. Sessions do not survive a restart with such a key.
if [ -z "$APP_KEY" ]; then
    APP_KEY="$(php artisan key:generate --show --no-ansi)"
    echo 'APP_KEY is not set, using a temporary key.' >&2
fi

# Render's generateValue yields a bare base64 string of 32 bytes; Laravel
# reads it as a base64 key only with the prefix.
case "$APP_KEY" in
    base64:*) ;;
    *) APP_KEY="base64:$APP_KEY" ;;
esac
export APP_KEY

touch database/database.sqlite
php artisan migrate --force --no-interaction

exec php artisan serve --no-reload --host=0.0.0.0 --port="${PORT:-8080}"
