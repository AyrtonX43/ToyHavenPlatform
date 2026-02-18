# ToyHaven Platform: Git → Hostinger Deployment Guide

Complete step-by-step instructions to import your code to Git, connect to Hostinger, and sync code + database on every development change.

---

## Important Clarifications

| What Syncs | How It Works |
|------------|--------------|
| **Code** | Git push → Hostinger pulls/clones → Your changes deploy |
| **Database schema** | `php artisan migrate` runs on Hostinger after deploy |
| **Database data** | Does NOT auto-sync. Local data stays local; production data stays production. Use migrations for schema, backups/imports for data. |

---

## Part 1: Initialize Git & Push to GitHub

### Step 1.1: Open PowerShell/Terminal in Project

```powershell
cd c:\xampp\htdocs\ToyHavenPlatform
```

### Step 1.2: Initialize Git Repository

```powershell
git init
```

### Step 1.3: Create Initial Commit (Ensure Sensitive Files Are Ignored)

Your `.gitignore` already excludes `.env`, `node_modules`, `vendor`, etc. **Never commit `.env`** — it has secrets.

Verify nothing sensitive is staged:

```powershell
git add .
git status
```

Review the list. Ensure you do NOT see: `.env`, `database/database.sqlite` (if it has real data), or any files with passwords/API keys.

If `database/database.sqlite` exists and has data you want to keep only locally, add it to `.gitignore`:

```
/database/database.sqlite
```

Then:

```powershell
git add .
git commit -m "Initial commit: ToyHaven Platform"
```

### Step 1.4: Create GitHub Repository

1. Go to [https://github.com/new](https://github.com/new)
2. Repository name: `ToyHavenPlatform` (or your choice)
3. **Do NOT** initialize with README, .gitignore, or license (you already have these)
4. Create repository
5. Copy the repository URL (e.g., `https://github.com/yourusername/ToyHavenPlatform.git`)

### Step 1.5: Add Remote and Push

```powershell
git remote add origin https://github.com/YOUR_USERNAME/ToyHavenPlatform.git
git branch -M main
git push -u origin main
```

Use your GitHub username. If prompted for credentials, use a Personal Access Token (PAT), not your password.

**For private repo:** Create a PAT: GitHub → Settings → Developer settings → Personal access tokens → Generate new token (classic) — enable `repo` scope.

---

## Part 2: Hostinger Preparation

### Step 2.1: Hostinger Plan Requirements

- **Business** or higher (for SSH access) — recommended for Laravel
- Or **VPS** if you need full control and post-deploy scripts

### Step 2.2: Create Website and Enable SSH

1. Log in to [hPanel](https://hpanel.hostinger.com)
2. **Websites** → **Add Website** → **Empty PHP/HTML website**
3. After creation: **Advanced** → **SSH Access** → **Enable** and set a password. Save it.

### Step 2.3: Create MySQL Database

1. In website dashboard: **Databases** → **Management**
2. Click **Create database**
3. Create database and user; note:
   - Database name (e.g., `u12345678_toyhaven`)
   - Username (e.g., `u12345678_toyhaven`)
   - Password
   - Host (usually `localhost`)

### Step 2.4: Get Your Domain Path

Note your site path, e.g.:

```
/home/u12345678/domains/yourdomain.com
```

Find it in **Files** → **File Manager** or in the site summary.

---

## Part 3: Connect Hostinger to Git

### Option A: Hostinger Built-in Git (Simpler)

1. In website dashboard: **Git** (left sidebar)
2. **Create a New Repository**:
   - **Repository Address:** `https://github.com/YOUR_USERNAME/ToyHavenPlatform.git`
   - **Branch:** `main`
   - **Install Path:** Leave empty (deploys to root) or set custom path
3. If repo is private:
   - Click **Add SSH Key** / **Manage Keys**
   - Generate SSH key in Hostinger
   - Add that key as a **Deploy Key** in GitHub: Repo → Settings → Deploy keys → Add
4. Click **Deploy**
5. Enable **Auto Deployment** and copy the **Webhook URL**
6. In GitHub: Repo → **Settings** → **Webhooks** → **Add webhook**:
   - Payload URL: paste Hostinger webhook URL
   - Content type: `application/json` or `application/x-www-form-urlencoded` (check Hostinger docs)
   - Events: **Just the push event**
   - Save

**Limitation:** Hostinger’s Git mainly clones/pulls. It does **not** run `composer install`, `npm run build`, or `php artisan migrate` automatically on shared hosting. You must run these via SSH after each deploy (or use Option B).

### Option B: GitHub Actions + SSH (Recommended for Laravel)

This runs `composer install`, `npm run build`, and `php artisan migrate` on every push.

---

## Part 4: First-Time Setup on Hostinger (Via SSH)

Before auto-deploy, do a one-time manual setup.

### Step 4.1: Connect via SSH

Use PuTTY (Windows) or Terminal (Mac/Linux):

- **Host:** `ssh.u12345678@yourdomain.com` (or the host from Hostinger)
- **Port:** 65002 (or the one shown in hPanel)
- **Username/Password:** From SSH Access settings

### Step 4.2: Go to Site Directory

```bash
cd /home/u12345678/domains/yourdomain.com
```

Replace with your actual path.

### Step 4.3: If Using Hostinger Git — Ensure Repo Is Deployed

If you already deployed via Hostinger Git, skip to Step 4.5.  
Otherwise, clone manually:

```bash
# Backup/remove existing content if needed
# rm -rf public_html/*   # Only if you're sure!

git clone https://github.com/YOUR_USERNAME/ToyHavenPlatform.git .
```

### Step 4.4: Install Dependencies

```bash
composer2 install --no-dev
npm install
npm run build
```

Use `composer2`; Hostinger has both Composer 1 and 2.

### Step 4.5: Create `.env` on Server

```bash
cp .env.example .env
nano .env   # or: vi .env
```

Set at least:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u12345678_toyhaven
DB_USERNAME=u12345678_toyhaven
DB_PASSWORD=your_actual_password
```

If password has special characters, wrap in quotes:

```env
DB_PASSWORD="your&password#here"
```

Save and exit (`Ctrl+X`, then `Y`, then Enter in nano).

### Step 4.6: Generate Key and Migrate

```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed   # if you use seeders
```

### Step 4.7: Storage Link (Hostinger Workaround)

Hostinger often disables `symlink()`. Use a hard link instead:

```bash
ln -s /home/u12345678/domains/yourdomain.com/storage/app/public /home/u12345678/domains/yourdomain.com/public/storage
```

Replace paths with yours.

### Step 4.8: Point `public_html` to Laravel `public`

```bash
# Remove default public_html content if deploying to root
# Then create symlink so Hostinger serves Laravel's public folder:
ln -sf /home/u12345678/domains/yourdomain.com/public /home/u12345678/domains/yourdomain.com/public_html
```

If Hostinger expects `public_html` as a folder with `index.php`, check their Laravel docs. Some setups require copying `public/*` into `public_html` or using an `.htaccess` in `public_html` that redirects to `../public`.

### Step 4.9: Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R u12345678:u12345678 storage bootstrap/cache
```

(Replace `u12345678` with your actual user if different.)

---

## Part 5: Auto-Sync Workflow (Code + Database Schema)

### Every Time You Change Code

1. Develop locally in Cursor
2. Run tests and migrations locally:

   ```powershell
   php artisan migrate
   npm run build
   ```

3. Commit and push:

   ```powershell
   git add .
   git commit -m "Description of changes"
   git push origin main
   ```

4. If using Hostinger webhook: Hostinger will pull automatically.
5. **Run post-deploy commands via SSH** (until you add GitHub Actions):

   ```bash
   cd /home/u12345678/domains/yourdomain.com
   git pull origin main
   composer2 install --no-dev
   npm run build
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Database Schema Sync (Not Data)

- **Schema:** Handled by `php artisan migrate`. Run it on Hostinger after each deploy.
- **Data:** Local and production stay separate. To move data, use:
  - Backups and restores
  - Custom scripts (`mysqldump`, Laravel seeders, etc.)
  - Or tools like Laravel Backup, etc.

---

## Part 6: GitHub Actions (Fully Automated Deploy)

This runs everything automatically on `git push`.

### Step 6.1: Add Hostinger SSH Key to GitHub

1. In Hostinger: **Git** → **Manage** → **SSH keys** (or similar)
2. Generate/copy the SSH key Hostinger uses for this site
3. In GitHub: Repo → **Settings** → **Deploy keys** → Add that key

### Step 6.2: Add GitHub Secrets

Repo → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**:

- `HOSTINGER_HOST` — SSH host (e.g., `ssh.u12345678@yourdomain.com`)
- `HOSTINGER_USER` — SSH username
- `HOSTINGER_PASSWORD` or `HOSTINGER_SSH_KEY` — SSH auth

### Step 6.3: Create Workflow File

Create `.github/workflows/deploy.yml` in your project:

```yaml
name: Deploy to Hostinger

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Install dependencies
        run: composer install --no-dev --prefer-dist

      - name: Build assets
        run: |
          npm ci
          npm run build

      - name: Deploy via SSH
        uses: appleboy/scp-action@v0.1.7
        with:
          host: ${{ secrets.HOSTINGER_HOST }}
          username: ${{ secrets.HOSTINGER_USER }}
          password: ${{ secrets.HOSTINGER_PASSWORD }}
          port: 65002
          source: "."
          target: "/home/u12345678/domains/yourdomain.com"
          strip_components: 0
          exclude: ".git*, .env, node_modules, .github"

      - name: Run post-deploy commands
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.HOSTINGER_HOST }}
          username: ${{ secrets.HOSTINGER_USER }}
          password: ${{ secrets.HOSTINGER_PASSWORD }}
          port: 65002
          script: |
            cd /home/u12345678/domains/yourdomain.com
            composer2 install --no-dev
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
```

Adjust paths, port, and secret names to match your Hostinger setup. Some Hostinger plans use SFTP/rsync instead of raw SSH — check their docs.

---

## Part 7: Checklist Before Going Live

- [ ] `.env` never committed (in `.gitignore`)
- [ ] `APP_DEBUG=false` and `APP_ENV=production` on server
- [ ] `APP_KEY` generated on server
- [ ] Database credentials correct in production `.env`
- [ ] `storage` and `bootstrap/cache` writable (775)
- [ ] `public/storage` link exists (or hard link if symlink disabled)
- [ ] `public_html` correctly points to Laravel `public`
- [ ] Migrations run and up to date
- [ ] Cron job set for `php artisan schedule:run` (if using schedulers)

### Hostinger Cron (Example)

In hPanel: **Advanced** → **Cron Jobs**:

```
/usr/bin/php /home/u12345678/domains/yourdomain.com/artisan schedule:run
```

Frequency: every minute (`* * * * *`).

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| `symlink()` disabled | Use `ln -s` manually for storage link (Step 4.7) |
| 500 error | Check `storage/logs/laravel.log`, permissions on `storage` and `bootstrap/cache` |
| DB connection error | Verify `DB_*` in `.env`, host usually `localhost` |
| Composer fails | Use `composer2` |
| Node/npm missing | Use NVM to install Node (see Hostinger/community guides) |
| `public_html` 404 | Ensure `public_html` points to Laravel `public` or contains correct `index.php` and `.htaccess` |

---

## Summary: Your Daily Workflow

1. Code in Cursor
2. `git add . && git commit -m "..." && git push`
3. Hostinger auto-pulls (webhook) or GitHub Actions deploys
4. If manual: SSH in and run `git pull`, `composer2 install`, `npm run build`, `php artisan migrate --force`
5. Database schema stays in sync via migrations; data does not sync automatically.

---

*Last updated: February 2025*
