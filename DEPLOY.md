# ToyHaven Platform - Deploy Instructions

## Git Workflow (Local)

After making changes:

```bash
git add .
git commit -m "feat: membership plan – admin terms, plan capabilities, PayPal demo"
git push origin main
```

(Adjust branch name if using something other than `main`.)

---

## Live Server - Pull and Deploy

### Option 1: Full Deploy Script (Recommended)

SSH into your server and run:

```bash
cd /var/www/toyhaven   # or your app path
bash scripts/deploy-digitalocean.sh main
```

This will: pull latest code, install dependencies, build assets, run migrations, cache config/routes/views, restart queue and Reverb.

### Option 2: Manual Pull

If you prefer to pull and deploy manually:

```bash
cd /var/www/toyhaven   # or your app path
git pull origin main

composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Restart queue worker and Reverb if needed:

```bash
sudo systemctl restart toyhaven-queue
sudo systemctl restart toyhaven-reverb   # if using Reverb
```

---

## First Deploy (New Membership Features)

If this is the first deploy of the membership plan system, run seeders after migration:

```bash
php artisan db:seed --class=PlanTermsSeeder
# or full seed:
php artisan db:seed
```

This seeds initial Terms & Conditions for Basic, Pro, and VIP plans.
