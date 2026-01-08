# Deployment Guide - BV Economic Dashboard

This guide covers deploying changes to cPanel when Node.js is not available on the server.

---

## Prerequisites

- Git installed locally
- Node.js and npm installed locally
- Access to cPanel terminal or SSH
- Repository access on GitHub

---

## Deployment Process

### **STEP 1: Build Assets Locally**

Since cPanel doesn't have Node.js, you must build assets on your local machine first.

```bash
cd /Users/shakir/Desktop/wk/bv-economic-dashboard

# Install dependencies (if needed)
npm install

# Build production assets
npm run build
```

This creates optimized CSS/JS files in `public/build/` directory.

---

### **STEP 2: Force Add Build Files to Git**

The `public/build` directory is in `.gitignore` by default, so you need to force-add it.

```bash
# Force add build files (ignore .gitignore)
git add -f public/build

# Verify what will be committed
git status
```

---

### **STEP 3: Commit All Changes**

```bash
# Add all other changed files
git add .

# Create commit with descriptive message
git commit -m "Your descriptive commit message here

- List your changes
- One per line
- Be specific

🤖 Generated with Claude Code

Co-Authored-By: Claude <noreply@anthropic.com>"
```

**Example commit message template:**
```
UI improvements and bug fixes

- Updated toolbar button sizes for better responsiveness
- Fixed link styling for invoice numbers
- Changed auto-sync interval to 1 hour
- Added active state for date filter buttons

🤖 Generated with Claude Code

Co-Authored-By: Claude <noreply@anthropic.com>
```

---

### **STEP 4: Push to GitHub**

```bash
# Push to your feature branch
git push origin feature/invoice-sync

# OR push to main branch
git push origin main
```

---

### **STEP 5: Deploy to cPanel**

#### **Option A: Using cPanel Terminal (Recommended)**

1. Login to cPanel
2. Open Terminal
3. Run deployment commands:

```bash
# Navigate to your project directory (UPDATE THIS PATH!)
cd /home/yourusername/public_html

# Pull latest changes
git pull origin main  # or your branch name

# Run database migrations (if any)
php artisan migrate --force

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize

# Verify auto-sync schedule
php artisan schedule:list

echo "✅ Deployment Complete!"
```

#### **Option B: One-Line Deployment Script**

Copy and paste this entire command (update the path first):

```bash
cd /home/yourusername/public_html && git pull origin main && php artisan migrate --force && php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache && composer dump-autoload --optimize && php artisan schedule:list && echo "✅ Deployment Complete!"
```

---

## Post-Deployment Verification

### 1. **Check the Website**
- Visit your dashboard URL
- Test all functionality
- Check for JavaScript errors (F12 → Console)
- Verify links work correctly
- Test buttons and interactions

### 2. **Verify Auto-Sync Schedule**
```bash
php artisan schedule:list
```

Should show your scheduled tasks with next run time.

### 3. **Check for Errors**
```bash
# View last 50 lines of log
tail -50 storage/logs/laravel.log

# Watch logs in real-time
tail -f storage/logs/laravel.log
```

Press `Ctrl+C` to stop watching logs.

### 4. **Test Database Migrations**
```bash
# Check migration status
php artisan migrate:status
```

All migrations should show "Ran".

---

## Common Issues & Solutions

### **Issue: Styles Not Updating**

**Solution:**
```bash
# On cPanel
php artisan view:clear
php artisan cache:clear

# In browser
Hard refresh: Ctrl+Shift+R (Windows/Linux) or Cmd+Shift+R (Mac)
```

---

### **Issue: Git Pull Fails with Conflicts**

**Solution:**
```bash
# Stash local changes
git stash

# Pull changes
git pull origin main

# Reapply stashed changes
git stash pop

# Or discard local changes entirely
git reset --hard origin/main
```

---

### **Issue: Permission Errors**

**Solution:**
```bash
# Fix storage and cache permissions
chmod -R 755 storage bootstrap/cache
chown -R username:username storage bootstrap/cache
```

---

### **Issue: Migration Fails**

**Solution:**
```bash
# Check migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback --step=1

# Re-run migrations
php artisan migrate --force
```

---

### **Issue: Auto-Sync Not Running**

**Solution:**

1. **Check cron job exists:**
```bash
crontab -l
```

Should show:
```
* * * * * cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1
```

2. **Add cron job if missing:**
```bash
crontab -e
```

Add this line (update path):
```
* * * * * cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1
```

3. **Verify schedule:**
```bash
php artisan schedule:list
```

---

## Deployment Checklist

Use this checklist for every deployment:

- [ ] **Local: Build assets** (`npm run build`)
- [ ] **Local: Force add build files** (`git add -f public/build`)
- [ ] **Local: Commit changes** (with descriptive message)
- [ ] **Local: Push to GitHub**
- [ ] **cPanel: Pull changes** (`git pull origin main`)
- [ ] **cPanel: Run migrations** (`php artisan migrate --force`)
- [ ] **cPanel: Clear caches**
- [ ] **cPanel: Optimize** (config, routes, views)
- [ ] **Test: Visit website** (verify changes work)
- [ ] **Test: Check console** (no JS errors)
- [ ] **Test: Check logs** (no PHP errors)
- [ ] **Test: Verify schedule** (auto-sync running)

---

## File Permissions Reference

If you encounter permission issues, here are the correct permissions:

```bash
# Directories should be 755
find . -type d -exec chmod 755 {} \;

# Files should be 644
find . -type f -exec chmod 644 {} \;

# Storage and cache need write permissions
chmod -R 775 storage bootstrap/cache

# Make sure owner is correct
chown -R username:username .
```

---

## Emergency Rollback

If deployment breaks the site:

```bash
# On cPanel
cd /home/username/public_html

# View recent commits
git log --oneline -5

# Rollback to previous commit (replace COMMIT_HASH)
git reset --hard COMMIT_HASH

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Useful Commands Reference

### **Git Commands**
```bash
git status                          # Check current status
git log --oneline -10              # View last 10 commits
git diff                           # View uncommitted changes
git diff HEAD~1                    # Compare with previous commit
git branch                         # List branches
git checkout -b new-branch         # Create new branch
```

### **Laravel Artisan Commands**
```bash
php artisan migrate:status         # Check migration status
php artisan migrate:rollback       # Rollback last migration
php artisan migrate:fresh          # Drop all tables and re-run migrations (DANGEROUS!)
php artisan route:list             # List all routes
php artisan schedule:list          # List scheduled tasks
php artisan schedule:run           # Manually run scheduler
php artisan queue:work             # Run queue worker
php artisan tinker                 # Laravel REPL
```

### **File Management**
```bash
ls -la                             # List files with permissions
du -sh *                           # Disk usage by directory
df -h                              # Disk space
tail -f storage/logs/laravel.log   # Watch logs
grep "error" storage/logs/laravel.log  # Search logs
```

---

## Notes

1. **Always build locally** before deploying (cPanel has no Node.js)
2. **Always force-add** `public/build` directory (`git add -f public/build`)
3. **Always clear caches** after deployment
4. **Always test** after deployment
5. **Keep commits small** for easier rollback if needed

---

## Support

- **Laravel Documentation**: https://laravel.com/docs
- **Git Documentation**: https://git-scm.com/doc
- **Project Issues**: Check `storage/logs/laravel.log`

---

**Last Updated:** January 8, 2026
**Author:** Claude Code
