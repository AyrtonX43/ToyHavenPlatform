# Git Sync Instructions – ToyHaven Platform

This guide walks you through syncing your local changes to Git (commit, push, and optionally create a branch).

---

## Prerequisites

- Git installed and configured
- Access to the remote repository (e.g. on GitHub, GitLab, or Bitbucket)
- Your remote set up (usually `origin`)

---

## 1. Check Current Status

Open a terminal (PowerShell or Command Prompt) in the project directory and run:

```powershell
cd c:\xampp\htdocs\ToyHavenPlatform
git status
```

This shows:
- Modified files
- Untracked files
- Current branch
- Whether you are ahead/behind the remote

---

## 2. Review What Changed

To see the diff for modified files:

```powershell
git diff
```

To see changes in a specific file:

```powershell
git diff resources/views/welcome.blade.php
git diff resources/views/layouts/admin.blade.php
git diff resources/views/layouts/seller.blade.php
```

---

## 3. Stage Files for Commit

**Option A – Stage all modified files:**

```powershell
git add .
```

**Option B – Stage specific files only:**

```powershell
git add resources/views/welcome.blade.php
git add resources/views/layouts/admin.blade.php
git add resources/views/layouts/seller.blade.php
```

**Option C – Stage interactively (choose chunks):**

```powershell
git add -p
```

To unstage a file:

```powershell
git reset HEAD resources/views/welcome.blade.php
```

---

## 4. Commit Your Changes

Create a commit with a clear message:

```powershell
git commit -m "feat: Make Auction clickable on homepage, update admin/seller UI with auction-style colors and animations"
```

Or a longer message:

```powershell
git commit -m "feat: Homepage and admin/seller UI updates

- Homepage: Auction card now clickable, links to auctions.index
- Admin layout: Auction-style colors (#0891b2), animations, sidebar tweaks
- Seller layout: Matching animations and color scheme"
```

Check the commit:

```powershell
git log -1 --stat
```

---

## 5. Push to Remote

**If you’re on the default branch (e.g. `main` or `master`):**

```powershell
git push origin main
```

Replace `main` with your branch name if needed (e.g. `master`).

**If you created a feature branch:**

```powershell
git push origin your-branch-name
```

**First push for a new branch:**

```powershell
git push -u origin your-branch-name
```

`-u` sets the upstream so future `git push` and `git pull` work without specifying the branch.

---

## 6. Full Workflow Example

```powershell
cd c:\xampp\htdocs\ToyHavenPlatform

# 1. See what changed
git status
git diff

# 2. Stage changes
git add resources/views/welcome.blade.php
git add resources/views/layouts/admin.blade.php
git add resources/views/layouts/seller.blade.php

# 3. Commit
git commit -m "feat: Auction link on homepage, admin/seller UI updates with auction colors and animations"

# 4. Push
git push origin main
```

---

## 7. Using a Feature Branch (Recommended)

If you prefer a feature branch before merging to `main`:

```powershell
cd c:\xampp\htdocs\ToyHavenPlatform

# Create and switch to a new branch
git checkout -b feature/homepage-auction-and-ui-updates

# Stage and commit
git add .
git commit -m "feat: Make Auction clickable on homepage, update admin/seller UI"

# Push the new branch
git push -u origin feature/homepage-auction-and-ui-updates
```

Then open a Pull Request (GitHub) or Merge Request (GitLab) to merge into `main`.

---

## 8. Pull Before Push (if others are working on the repo)

To avoid conflicts, pull first:

```powershell
git pull origin main
# Fix any merge conflicts if prompted
git add .
git commit -m "Merge remote changes"
git push origin main
```

---

## 9. Undo Last Commit (if needed)

If you committed by mistake and haven’t pushed:

```powershell
git reset --soft HEAD~1
```

This keeps your changes staged; you can modify and commit again.

---

## 10. Summary of Changes in This Update

| File | Changes |
|------|---------|
| `resources/views/welcome.blade.php` | Auction card is now a link to `/auctions`, uses “Bid Now” button instead of disabled “Coming Soon” |
| `resources/views/layouts/admin.blade.php` | Auction-style colors (#0891b2), sidebar icon animation, mobile toggle styling, form focus colors |
| `resources/views/layouts/seller.blade.php` | Page content animation, sidebar icon animation, background color aligned with admin |

---

## Troubleshooting

**“Updates were rejected because the remote contains work…”**

- Someone else pushed to the branch. Pull and then push:

```powershell
git pull origin main --rebase
git push origin main
```

**“Authentication failed”**

- Use a personal access token instead of a password, or set up SSH keys for the remote.

**“Nothing to commit, working tree clean”**

- All changes are committed. Check `git status` and `git diff` to confirm.
