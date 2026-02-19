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

You have two ways to connect your GitHub repo to Hostinger:

| Option | Best for | Auto runs composer, npm, migrate? |
|--------|----------|-----------------------------------|
| **A: Hostinger Built-in Git** | Simple sites, quick setup | No — run manually via SSH after each deploy |
| **B: GitHub Actions** | Laravel, full automation | Yes — runs automatically on every push |

---

### Option A: Hostinger Built-in Git (Simpler)

Hostinger can clone or pull your Git repository and deploy files automatically when you push. You run Laravel commands (composer, npm, migrate) yourself via SSH.

#### Step 3A.1: Open Git Settings in hPanel

1. Log in to [hPanel](https://hpanel.hostinger.com)
2. Go to **Websites** → click **Manage** next to your domain
3. In the left sidebar, search for **Git** or look under **Advanced** or **Deployment**
4. Click **Git** to open the Git management page

#### Step 3A.2: Add Your Repository

1. In **Create a New Repository** (or **Add Repository**), fill in:
   - **Repository Address:**
     - HTTPS: `https://github.com/YOUR_USERNAME/ToyHavenPlatform.git`
     - Or SSH: `git@github.com:YOUR_USERNAME/ToyHavenPlatform.git` (needs SSH key setup in Step 3A.3)
   - **Branch:** `main` (or your default branch)
   - **Install Path:**
     - Leave **empty** to deploy to your account root (e.g. `/home/u12345678/domains/yourdomain.com`)
     - Or enter a subfolder (e.g. `public_html/site`) to deploy only there

2. Click **Create** or **Add Repository**

#### Step 3A.3: Private Repository — Add SSH Deploy Key

For private GitHub repos, Hostinger must authenticate using an SSH deploy key.

**In Hostinger (Git section):**

1. Find **Manage Keys**, **Add SSH Key**, or **Deploy Keys**
2. Click **Generate** or **Create new key**
3. Copy the **public key** (starts with `ssh-rsa` or `ssh-ed25519`). Save it; it may not be shown again.

**In GitHub:**

1. Open your repo → **Settings** → **Deploy keys** (under Security)
2. Click **Add deploy key**
3. **Title:** e.g. `Hostinger production`
4. **Key:** paste the full public key from Hostinger
5. Leave **Allow write access** unchecked (read-only is enough for deploy)
6. Click **Add key**

**Back in Hostinger:**

- Use the SSH repo URL: `git@github.com:YOUR_USERNAME/ToyHavenPlatform.git`

#### Step 3A.4: Deploy for the First Time

1. In the Git section, find your repository
2. Click **Deploy** (or **Pull**)
3. Wait for the deploy to finish; check the build/output log if available
4. Files from the repo will appear at the Install Path (or root if empty)

#### Step 3A.5: Enable Auto Deployment (Webhook)

To deploy automatically on every `git push`:

**In Hostinger:**

1. In the Git section, find your repository
2. Click **Auto Deployment** or **Manage**
3. Turn on **Auto Deployment**
4. Copy the **Webhook URL** (similar to `https://...hostinger.../deploy/...`). Save it.

**In GitHub:**

1. Open your repo → **Settings** → **Webhooks**
2. Click **Add webhook**
3. Set:
   - **Payload URL:** paste the Hostinger webhook URL
   - **Content type:** `application/json` (if offered) or `application/x-www-form-urlencoded` — follow Hostinger’s instructions
   - **Secret:** leave empty unless Hostinger provides one
   - **Which events:** select **Just the push event**
4. Click **Add webhook**
5. GitHub sends a test ping; check for a green checkmark. If it fails, verify URL and content type.

#### Step 3A.6: Verify Webhook

1. Make a small local change (e.g. add a comment)
2. Commit and push: `git push origin main`
3. In Hostinger’s Git section, check the deploy log — a new deploy should appear
4. If not, in GitHub → **Settings** → **Webhooks** → click your webhook → **Recent Deliveries** to inspect delivery and response

#### Option A Limitation

Hostinger’s Git only clones/pulls files. It does **not** run:

- `composer install`
- `npm install` / `npm run build`
- `php artisan migrate`

After each deploy, SSH in and run these yourself (see Part 5 for commands).

---

### Option B: GitHub Actions + SSH (Recommended for Laravel)

GitHub Actions runs a workflow on each push: builds the app (composer, npm), deploys to Hostinger via SSH, and runs `php artisan migrate` and cache commands. No manual SSH deploy steps needed.

#### How It Works

1. You push to `main` on GitHub
2. The workflow runs
3. It installs dependencies, builds assets, copies files to Hostinger via SSH/SCP, then runs deploy commands on the server

#### When to Use Option B

- You want full automation (composer, npm, migrate) on every push
- You’re okay adding a workflow file and GitHub secrets
- You have Hostinger SSH access (Business plan or higher)

#### Setup Overview

1. Add GitHub **Secrets** for Hostinger (host, user, password or SSH key)
2. Add the workflow file `.github/workflows/deploy.yml` (see Part 6)
3. Push to `main` — the first deployment runs automatically

Part 6 has the complete GitHub Actions workflow and setup.

---

## Part 4: First-Time Setup on Hostinger (Via SSH)

Before auto-deploy, do a one-time manual setup.

### Step 4.1: Connect via SSH

You need SSH access to run commands directly on your Hostinger server. SSH credentials are **different** from your Hostinger account login.

#### Where to Find Your SSH Credentials in Hostinger

1. Log in to [hPanel](https://hpanel.hostinger.com)
2. Go to **Websites** → click **Manage** next to your domain
3. In the left sidebar, go to **Advanced** → **SSH Access**
4. Ensure **SSH Access** is **Enabled** (toggle on if off)
5. You will see (or can set):
   - **Hostname** — e.g. `ssh.u12345678.yourdomain.hostingersite.com` or `yourdomain.com`
   - **Port** — usually `65002` (Hostinger uses a non-standard port)
   - **Username** — e.g. `u12345678` (your account ID)
   - **Password** — set this here if you haven’t already; you’ll use it to log in via SSH

If SSH is disabled, enable it, set a strong password, and save.

#### Option A: PuTTY (Windows)

1. **Download PuTTY**
   - [https://www.putty.org/](https://www.putty.org/) → download the Windows installer
   - Or use Windows Terminal / PowerShell with OpenSSH if you prefer

2. **Open PuTTY** and enter:
   - **Host Name:** `ssh.u12345678.yourdomain.hostingersite.com` (use the exact host from hPanel)
   - **Port:** `65002` (or the port shown in hPanel)
   - **Connection type:** SSH

3. Click **Open**

4. If prompted **“The server’s host key is not cached in the registry”**:
   - Click **Yes** to trust the server (first-time only)

5. When prompted **“login as:”** — type your SSH username (e.g. `u12345678`)

6. When prompted **“password:”** — type your SSH password (nothing will appear as you type; this is normal)

7. On success, you’ll see a shell prompt such as:
   ```
   u12345678@server:~$
   ```

#### Option B: Windows Terminal / PowerShell (Windows 10/11)

1. Open **Terminal** or **PowerShell**

2. Run (replace with your host, port, and username):
   ```powershell
   ssh -p 65002 u12345678@ssh.u12345678.yourdomain.hostingersite.com
   ```

3. Enter your SSH password when prompted.

#### Option C: Terminal (Mac / Linux)

1. Open **Terminal**

2. Run (replace with your host, port, and username):
   ```bash
   ssh -p 65002 u12345678@ssh.u12345678.yourdomain.hostingersite.com
   ```

3. If asked **“Are you sure you want to continue connecting?”** — type `yes` and press Enter.

4. Enter your SSH password when prompted.

#### Verifying the Connection

Once connected, run:

```bash
pwd
```

You should be in your home directory (e.g. `/home/u12345678`). You can then `cd` to your site directory in Step 4.2.

#### Troubleshooting SSH Connection

| Problem | What to try |
|---------|--------------|
| **Connection refused** | Confirm SSH is enabled in hPanel, and use the correct port (often 65002) |
| **Connection timed out** | Check firewall/antivirus; ensure your host and port match hPanel |
| **Access denied / invalid password** | Reset SSH password in hPanel → SSH Access |
| **Host key verification failed** | Remove the old key: `ssh-keygen -R ssh.u12345678.yourdomain.hostingersite.com` |
| **PuTTY: “No supported authentication methods”** | Verify username and that password authentication is allowed in Hostinger SSH settings |

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
