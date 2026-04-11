#!/usr/bin/env bash
set -euo pipefail

ENV_FILE="${ENV_FILE:-/var/www/antigores/.env}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/antigores-db}"
RETENTION_DAYS="${RETENTION_DAYS:-14}"

if [ ! -f "$ENV_FILE" ]; then
  echo "ENV file tidak ditemukan: $ENV_FILE"
  exit 1
fi

set -a
source "$ENV_FILE"
set +a

mkdir -p "$BACKUP_DIR"

db_host="${DB_HOST:-127.0.0.1}"
db_port="${DB_PORT:-3306}"
db_name="${DB_DATABASE:-antigores}"
db_user="${DB_USERNAME:-root}"
db_pass="${DB_PASSWORD:-}"

timestamp="$(date +%Y%m%d-%H%M%S)"
file="$BACKUP_DIR/${db_name}-${timestamp}.sql.gz"

mysqldump -h "$db_host" -P "$db_port" -u "$db_user" --password="$db_pass" --single-transaction --quick "$db_name" | gzip > "$file"

find "$BACKUP_DIR" -type f -name "*.sql.gz" -mtime +"$RETENTION_DAYS" -delete

echo "Backup created: $file"
