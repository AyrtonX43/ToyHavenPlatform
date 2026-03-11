#!/bin/bash
# ToyHavenPlatform - Deploy to Live Server via SSH/PuTTY
# Run this on your live server after connecting via PuTTY/SSH
# Or copy-paste the commands below manually

# Replace with your actual project path on the server
cd /path/to/ToyHavenPlatform

# Pull latest changes
git pull origin main

# Run migrations if any new ones exist
php artisan migrate --force

# Clear and rebuild caches
php artisan config:cache
php artisan view:clear
php artisan route:clear

echo "Deployment complete."
