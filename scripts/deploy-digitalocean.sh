#!/usr/bin/env bash
# ToyHaven Platform — Deploy script for DigitalOcean Droplet
# Run on the server: bash /var/www/toyhaven/scripts/deploy-digitalocean.sh [branch]
# Usage: ./scripts/deploy-digitalocean.sh [branch]
# Example: ./scripts/deploy-digitalocean.sh main

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_PATH="$(cd "$SCRIPT_DIR/.." && pwd)"
BRANCH="${1:-main}"

echo "==> Deploying ToyHaven Platform (branch: $BRANCH) at $APP_PATH"
cd "$APP_PATH" || exit 1

echo "==> Pulling latest code..."
git fetch origin
git checkout "$BRANCH"
git pull origin "$BRANCH"

echo "==> Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Installing Node dependencies and building assets..."
npm ci
npm run build

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Caching config, routes, views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Restarting queue worker..."
sudo systemctl restart toyhaven-queue

if systemctl is-active --quiet toyhaven-reverb 2>/dev/null; then
    echo "==> Restarting Reverb..."
    sudo systemctl restart toyhaven-reverb
fi

echo "==> Deploy complete."
