# Live Server Deploy (PuTTY / SSH)

After pushing changes from your local machine, pull on the live server:

```bash
cd /path/to/ToyHavenPlatform
git pull origin main
php artisan migrate --force
php artisan config:cache
php artisan view:cache
```

Replace `/path/to/ToyHavenPlatform` with your actual project path (e.g. `/var/www/toyhaven`).

For full deploy including dependencies and assets, use:

```bash
bash scripts/deploy-digitalocean.sh main
```
