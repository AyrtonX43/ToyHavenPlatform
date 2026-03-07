#!/bin/bash
# Deploy membership plan changes: commit, push, then run the pull command on live server
cd "$(dirname "$0")/.."

echo ""
echo "=== Git status ==="
git status

echo ""
echo "=== Adding and committing ==="
git add -A
if git diff --cached --quiet; then
    echo "No changes to commit."
else
    git commit -m "feat: membership plan enhancements - admin terms, capabilities, PayPal demo"
fi

echo ""
echo "=== Pushing to remote ==="
git push

echo ""
echo "============================================================"
echo "On live server (via PuTTY/SSH), run:"
echo ""
echo '  cd /path/to/ToyHavenPlatform && git pull && php artisan migrate --force && php artisan config:cache'
echo ""
echo "Replace /path/to/ToyHavenPlatform with your actual project path."
echo "============================================================"
echo ""
