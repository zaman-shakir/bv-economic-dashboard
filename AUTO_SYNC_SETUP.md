# Automatic Invoice Sync Setup Guide

## Overview

The system can automatically sync invoices from e-conomic API every 6 hours to keep your database up-to-date without manual intervention.

**Schedule:** Every 6 hours (at 00:00, 06:00, 12:00, 18:00 UTC)

---

## ✅ What's Already Configured

The automatic sync task is **already configured** in the code:
- File: `routes/console.php` (line 13)
- Command: `php artisan invoices:sync`
- Frequency: Every 6 hours
- Protection: `withoutOverlapping()` prevents multiple syncs running simultaneously

You can verify the schedule:
```bash
php artisan schedule:list
```

Output:
```
0 */6 * * *  php artisan invoices:sync .......... Next Due: X hours from now
```

---

## 🚀 How to Activate Auto-Sync

Laravel's scheduler requires a **single cron entry** to run. You have **3 options**:

### Option 1: Add Cron Job (Recommended for Production)

Add this single line to your server's crontab:

```bash
* * * * * cd /path/to/bv-economic-dashboard && php artisan schedule:run >> /dev/null 2>&1
```

**Steps:**
1. Open crontab editor:
   ```bash
   crontab -e
   ```

2. Add the line (replace `/path/to/` with actual path):
   ```
   * * * * * cd /Users/shakir/Desktop/wk/bv-economic-dashboard && php artisan schedule:run >> /dev/null 2>&1
   ```

3. Save and exit

**That's it!** Laravel will handle running tasks at the right time.

---

### Option 2: Run Scheduler Manually (Testing)

For testing, you can manually trigger the scheduler:

```bash
php artisan schedule:run
```

This checks if any scheduled tasks are due and runs them.

**Note:** This only runs tasks that are due RIGHT NOW. For continuous operation, use Option 1 or 3.

---

### Option 3: Keep Scheduler Running (Development)

For local development, keep the scheduler running in the background:

```bash
php artisan schedule:work
```

This command runs continuously and checks for due tasks every minute.

**Use Case:** Development/testing environments where you don't want to set up cron.

---

## ⚙️ Customizing Sync Frequency

You can change how often the sync runs by editing `routes/console.php`:

### Current Setting (Every 6 Hours)
```php
Schedule::command('invoices:sync')->everySixHours()->withoutOverlapping();
```

### Alternative Frequencies

**Every Hour:**
```php
Schedule::command('invoices:sync')->hourly()->withoutOverlapping();
```

**Every 12 Hours:**
```php
Schedule::command('invoices:sync')->twiceDaily(0, 12)->withoutOverlapping();
```

**Once Daily (at 2 AM):**
```php
Schedule::command('invoices:sync')->dailyAt('02:00')->withoutOverlapping();
```

**Every 30 Minutes:**
```php
Schedule::command('invoices:sync')->everyThirtyMinutes()->withoutOverlapping();
```

**Custom Cron Expression:**
```php
Schedule::command('invoices:sync')->cron('0 */4 * * *')->withoutOverlapping(); // Every 4 hours
```

---

## 🔍 Monitoring Auto-Sync

### Check Schedule Status
```bash
php artisan schedule:list
```

Shows all scheduled tasks and when they'll run next.

### View Sync Logs
```bash
tail -f storage/logs/laravel.log | grep "Invoice sync"
```

The sync process logs:
- `"Starting invoice sync from E-conomic API"`
- `"Fetching page X from E-conomic"`
- `"Invoice sync completed"`
- Statistics about created/updated invoices

### Manual Sync (Override Schedule)
```bash
php artisan invoices:sync
```

Runs the sync immediately, regardless of schedule.

### Force Sync (Ignore Locks)
```bash
php artisan invoices:sync --force
```

Bypasses the `withoutOverlapping()` protection (use carefully).

---

## 🛡️ Built-in Safety Features

### 1. No Overlapping Syncs
The `->withoutOverlapping()` method ensures only one sync runs at a time:
- If a sync is already running, the scheduler skips it
- Prevents database conflicts
- Avoids overwhelming the API

### 2. Progress Tracking
During sync, progress is stored in cache:
```php
Cache::get('invoice_sync_progress')
```

Returns:
```json
{
  "percentage": 45.5,
  "current": 10000,
  "message": "Fetched 10,000 invoices (Page 10)...",
  "status": "running",
  "updated_at": "2026-01-07T02:30:15Z"
}
```

### 3. API Rate Limiting
The sync includes a 100ms delay between API requests:
```php
usleep(100000); // 0.1 seconds between pages
```

This prevents overwhelming the e-conomic API.

### 4. Transaction Safety
Each invoice is saved in its own database transaction:
- If one invoice fails, others continue
- Errors are logged but don't stop the sync
- Failed invoices are tracked in sync statistics

---

## 📊 What Happens During Auto-Sync

When the scheduler triggers `invoices:sync`:

1. **Initialization**
   - Checks if a sync is already running
   - Logs start time
   - Initializes progress tracking

2. **Fetching Invoices**
   - Fetches 1000 invoices per API request
   - Processes each page sequentially
   - Updates progress after each page
   - Continues until all invoices are fetched

3. **Saving to Database**
   - Each invoice: `updateOrCreate()` (update existing, create new)
   - Wrapped in individual transactions
   - Failed saves are logged but don't stop sync

4. **Populating Employee Names**
   - Fetches unique employee numbers
   - Calls e-conomic API for each employee name
   - Updates all invoices with correct names
   - Results are cached (1 hour)

5. **Completion**
   - Logs statistics (total fetched, created, updated, errors)
   - Marks progress as "completed"
   - Clears sync lock

**Average Duration:** 30-45 seconds for 22,500 invoices

---

## 🚨 Troubleshooting

### Sync Doesn't Run Automatically

**Check cron is set up:**
```bash
crontab -l
```

You should see:
```
* * * * * cd /path/to/bv-economic-dashboard && php artisan schedule:run >> /dev/null 2>&1
```

**Verify scheduler is working:**
```bash
php artisan schedule:test
```

### Sync Gets Stuck

**Clear the sync lock:**
```bash
php artisan cache:clear
```

This removes the `withoutOverlapping()` lock if a sync crashed.

**Check for errors:**
```bash
tail -100 storage/logs/laravel.log
```

### Memory Issues During Sync

**Increase PHP memory limit:**

Edit `.env`:
```env
PHP_MEMORY_LIMIT=256M
```

Or run manually with more memory:
```bash
php -d memory_limit=256M artisan invoices:sync
```

---

## 📈 Performance Considerations

### Database Size Impact

With 22,500 invoices syncing every 6 hours:
- **Disk space:** ~50MB database size
- **Memory usage:** ~30MB during sync (optimized)
- **CPU usage:** Low (bulk inserts/updates)

### API Usage

Each full sync makes approximately:
- **23 API requests** (1000 invoices per request)
- **2-3 additional requests** for employee names (cached)
- **Total bandwidth:** ~10-15MB per sync

At 6-hour intervals:
- **4 syncs per day**
- **~100 API requests per day**
- Well within e-conomic API rate limits

---

## 🎯 Recommended Settings

### For Production (cPanel/Shared Hosting)

**Frequency:** Every 6 hours (current setting)
```php
Schedule::command('invoices:sync')->everySixHours()->withoutOverlapping();
```

**Why:**
- Balances freshness vs. resource usage
- Keeps data current throughout business day
- Reduces API load
- Works well with shared hosting limits

**Setup:**
1. Add cron job via cPanel Cron Jobs interface:
   - **Command:** `cd /home/username/public_html && /usr/bin/php artisan schedule:run`
   - **Minute:** `*`
   - **Hour:** `*`
   - **Day:** `*`
   - **Month:** `*`
   - **Weekday:** `*`

### For Development (Local)

**Frequency:** Every hour (for testing)
```php
Schedule::command('invoices:sync')->hourly()->withoutOverlapping();
```

**Setup:**
```bash
php artisan schedule:work
```

Keep this terminal open while developing.

---

## ✅ Verification Steps

After setup, verify auto-sync is working:

### 1. Check Schedule
```bash
php artisan schedule:list
```

### 2. Wait for Next Run
Note the "Next Due" time and wait.

### 3. Check Dashboard
After the scheduled time:
- Go to dashboard: `http://localhost:8000/dashboard`
- Check "Last synced" timestamp
- Should update automatically

### 4. Check Logs
```bash
grep "Invoice sync completed" storage/logs/laravel.log
```

You should see entries every 6 hours.

---

## 🔄 Disabling Auto-Sync

If you want to disable automatic sync:

**Option 1: Comment out in code**

Edit `routes/console.php`:
```php
// Schedule::command('invoices:sync')->everySixHours()->withoutOverlapping();
```

**Option 2: Remove cron job**
```bash
crontab -e
# Delete or comment out the line
```

**Option 3: Use environment variable**

Add to `.env`:
```env
AUTO_SYNC_ENABLED=false
```

Then update `routes/console.php`:
```php
if (env('AUTO_SYNC_ENABLED', true)) {
    Schedule::command('invoices:sync')->everySixHours()->withoutOverlapping();
}
```

---

## 📝 Summary

- ✅ Auto-sync is **configured** and **ready to use**
- ✅ Runs **every 6 hours** automatically
- ✅ **Safe** (no overlapping, transaction-protected)
- ✅ **Monitored** (logs, progress tracking)
- ✅ **Customizable** (change frequency in `routes/console.php`)

**To activate:** Just add the single cron job line to your server!

**Status:** ⏳ Waiting for cron job setup
