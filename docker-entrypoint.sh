#!/bin/bash
set -e

CONFIG_FILE="/var/www/html/public_html/includes/config.inc.php"

if [ ! -f "$CONFIG_FILE" ]; then
  echo "No config found — running LiteCart installer..."

  # Wait for MySQL to accept connections
  until php -r "new PDO('mysql:host=${DB_SERVER:-mysql};port=3306', '${DB_USERNAME:-litecart}', '${DB_PASSWORD:-litecart}');" 2>/dev/null; do
    echo "Waiting for MySQL..."
    sleep 2
  done

  cd /var/www/html/public_html/install

  php install.php \
    --document_root="/var/www/html/public_html" \
    --db_server="${DB_SERVER:-mysql}" \
    --db_username="${DB_USERNAME:-litecart}" \
    --db_password="${DB_PASSWORD:-litecart}" \
    --db_database="${DB_DATABASE:-litecart}" \
    --db_collation="${DB_COLLATION:-utf8mb4_general_ci}" \
    --db_table_prefix="${DB_TABLE_PREFIX:-lc_}" \
    --timezone="${TZ:-UTC}" \
    --username="${ADMIN_USERNAME:-admin}" \
    --password="${ADMIN_PASSWORD:-admin}"

  cd /var/www/html

  echo ""
  echo "============================================"
  echo " LiteCart installed successfully!"
  echo " Admin: http://localhost:9080/admin/"
  echo " User:  ${ADMIN_USERNAME:-admin}"
  echo " Pass:  ${ADMIN_PASSWORD:-admin}"
  echo "============================================"
  echo ""
else
  echo "Config found — skipping installation."
fi

exec apache2-foreground
