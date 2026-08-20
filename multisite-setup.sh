#!/usr/bin/env bash
# One-shot setup script run by the wpcli service in compose.multisite.yaml.
# Converts a fresh WordPress into a subdirectory-based Multisite Network and
# creates two subsites for testing. Idempotent: skips if already installed.
set -euo pipefail

cd /var/www/html

echo 'Waiting for WordPress files (created by the wordpress container on first start)...'
until [ -f wp-config.php ]; do sleep 2; done

# Use the mariadb client directly: the client in the wordpress:cli image
# requires TLS by default, which wp-cli's db commands cannot switch off.
echo 'Waiting for the database...'
until mariadb --host="$WORDPRESS_DB_HOST" --user="$WORDPRESS_DB_USER" \
  --password="$WORDPRESS_DB_PASSWORD" --skip-ssl \
  -e 'SELECT 1' >/dev/null 2>&1; do sleep 2; done

if wp core is-installed 2>/dev/null; then
  echo 'WordPress is already installed, skipping multisite setup.'
else
  echo 'Installing WordPress Multisite (subdirectory mode)...'
  wp core multisite-install \
    --url='http://localhost' \
    --title='attachmentAV Network' \
    --admin_user='admin' \
    --admin_password='admin' \
    --admin_email='admin@example.com' \
    --skip-email

  # Rewrite rules for subdirectory multisite (what Network Setup asks you to
  # paste into .htaccess). Without these, subsites like /site1/ return 404.
  cat > .htaccess <<'EOF'
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]

# add a trailing slash to /wp-admin
RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$ $1wp-admin/ [R=301,L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) $2 [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(.*\.php)$ $2 [L]
RewriteRule . index.php [L]
EOF

  echo 'Creating test subsites...'
  wp site create --slug='site1' --title='Test Site 1' --email='admin@example.com'
  wp site create --slug='site2' --title='Test Site 2' --email='admin@example.com'
fi

echo ''
echo 'Multisite network is ready.'
echo '  Network admin: http://localhost/wp-admin/network/ (admin / admin)'
echo '  Main site:     http://localhost/'
echo '  Subsites:      http://localhost/site1/  http://localhost/site2/'
echo ''
echo 'To network-activate the plugin:'
echo '  docker compose -f compose.multisite.yaml run --rm wpcli wp plugin activate attachmentav --network'
echo 'To activate on a single subsite instead:'
echo '  docker compose -f compose.multisite.yaml run --rm wpcli wp plugin activate attachmentav --url=http://localhost/site1'
