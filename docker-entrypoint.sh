#!/bin/bash
set -e

CONFIG_FILE="/var/www/html/public_html/includes/config.inc.php"

if [ ! -f "$CONFIG_FILE" ]; then
  echo "No config found — running LiteCart installer..."

  # Wait for MySQL to accept connections (mysqli — matches the rest of v2; PDO is no longer used)
  MAX_ATTEMPTS=30
  ATTEMPT=0
  until php -r "mysqli_report(MYSQLI_REPORT_OFF); @mysqli_connect('${DB_SERVER:-mysql}', '${DB_USERNAME:-litecart}', '${DB_PASSWORD:-litecart}') ?: exit(1);" 2>/dev/null; do
    ATTEMPT=$((ATTEMPT + 1))
    if [ "$ATTEMPT" -ge "$MAX_ATTEMPTS" ]; then
      echo "ERROR: Could not connect to MySQL at '${DB_SERVER:-mysql}' after $((MAX_ATTEMPTS * 2))s." >&2
      echo "       If you started the container with plain 'docker run', the 'mysql' hostname doesn't resolve" >&2
      echo "       — use 'docker compose up' (which creates the shared network) or set DB_SERVER to a reachable host." >&2
      exit 1
    fi
    echo "Waiting for MySQL... (${ATTEMPT}/${MAX_ATTEMPTS})"
    sleep 2
  done

  php /var/www/html/public_html/install/install.php \
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
