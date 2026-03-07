#!/usr/bin/env bash
# ToyHaven — Local commit and push (use before deploying to live)
# Usage: ./scripts/deploy-commit.sh "feat(membership): description"
# Or:    ./scripts/deploy-commit.sh

set -e

MSG="${1:-feat(membership): updates}"
cd "$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo "==> Staging changes..."
git add .

echo "==> Committing: $MSG"
git commit -m "$MSG" || true

echo "==> Pushing to origin..."
git push origin

echo "==> Done. On live server, run: git pull origin main && php artisan migrate --force && php artisan config:cache && php artisan view:cache"
