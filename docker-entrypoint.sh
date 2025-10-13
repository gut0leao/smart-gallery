#!/bin/bash
set -e

# docker-entrypoint.sh - performs initial WordPress setup if needed
# This script waits for the database provided via WORDPRESS_DB_* env vars
# and then installs WordPress if not already installed.

DB_HOST=${WORDPRESS_DB_HOST:-127.0.0.1:3306}
DB_NAME=${WORDPRESS_DB_NAME:-wordpress}
DB_USER=${WORDPRESS_DB_USER:-root}
DB_PASS=${WORDPRESS_DB_PASSWORD:-example}
WP_PATH=/var/www/html

wait_for_db() {
    # split host and port
    HOST_ONLY=${DB_HOST%%:*}
    PORT_ONLY=${DB_HOST##*:}
    echo "Waiting for DB at ${HOST_ONLY}:${PORT_ONLY}..."
    until nc -z "$HOST_ONLY" "$PORT_ONLY"; do
        echo "DB not ready, sleeping 1s..."
        sleep 1
    done
    echo "DB is up"
}

if [ -z "$(wp core is-installed --path="$WP_PATH" 2>/dev/null || true)" ]; then
    echo "Installing WordPress..."
    wait_for_db

    wp core download --path="$WP_PATH" --allow-root
    wp config create --dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" --dbhost="$DB_HOST" --path="$WP_PATH" --allow-root
    # try creating DB (may already exist for managed DBs)
    wp db create --path="$WP_PATH" --allow-root || true
    wp core install --url="http://localhost:8080" --title="Smart Gallery Demo" --admin_user="admin" --admin_password="admin" --admin_email="admin@example.com" --path="$WP_PATH" --allow-root

    echo "Installing required plugins..."
    wp plugin install elementor --activate --path="$WP_PATH" --allow-root || true
    wp plugin install pods --activate --path="$WP_PATH" --allow-root || true

    echo "Activating Smart Gallery plugin..."
    wp plugin activate smart-gallery --path="$WP_PATH" --allow-root || true
else
    echo "WordPress already installed. Skipping initial install."
fi

# Execute passed command (e.g., apache2-foreground)
exec "$@"
