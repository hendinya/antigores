#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/antigores}"
REPO_URL="${REPO_URL:-https://github.com/hendinya/antigores.git}"
BRANCH="${BRANCH:-main}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
NPM_BIN="${NPM_BIN:-npm}"
WEB_USER="${WEB_USER:-www-data}"

if [ ! -d "$APP_DIR/.git" ]; then
  rm -rf "$APP_DIR"
  git clone -b "$BRANCH" "$REPO_URL" "$APP_DIR"
fi

cd "$APP_DIR"
git fetch origin "$BRANCH"
git checkout "$BRANCH"
git reset --hard "origin/$BRANCH"

$COMPOSER_BIN install --no-dev --prefer-dist --optimize-autoloader --no-interaction

if [ -f package.json ]; then
  $NPM_BIN install --no-audit --no-fund
  $NPM_BIN run build
fi

if [ ! -f .env ]; then
  cp .env.example .env
fi

$PHP_BIN artisan key:generate --force
$PHP_BIN artisan migrate --force
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan optimize

chown -R "$WEB_USER":"$WEB_USER" storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
