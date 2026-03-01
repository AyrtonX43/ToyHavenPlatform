#!/bin/bash

# Deployment Script for ToyHaven Live Server
# Run this on your live server via PuTTY

echo "=========================================="
echo "ToyHaven - Live Server Deployment"
echo "=========================================="
echo ""

# Navigate to project directory
cd /home/u334258035/domains/toyhaven.online || exit 1
echo "✓ Changed to project directory"

# Pull latest code
echo ""
echo "Pulling latest code from GitHub..."
git pull origin main
echo "✓ Code updated"

# Install dependencies
echo ""
echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader
echo "✓ Dependencies installed"

# Create necessary directories
echo ""
echo "Creating receipt directory..."
mkdir -p storage/app/public/receipts
chmod -R 775 storage/app/public/receipts
echo "✓ Receipt directory created"

# Fix permissions
echo ""
echo "Fixing storage permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache
echo "✓ Permissions fixed"

# Create storage link
echo ""
echo "Creating storage symlink..."
php artisan storage:link
echo "✓ Storage linked"

# Clear all caches
echo ""
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "✓ Caches cleared"

# Cache config and routes
echo ""
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
echo "✓ Configuration cached"

# Check if web server restart is needed
echo ""
echo "=========================================="
echo "Deployment Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Make sure .env has live PayMongo keys"
echo "2. Test payment with amount ≥ ₱20"
echo "3. Check if receipt is generated"
echo "4. Verify email is sent"
echo ""
echo "If you need to restart web server:"
echo "  sudo systemctl restart apache2"
echo "  # or"
echo "  sudo systemctl restart nginx"
echo ""
