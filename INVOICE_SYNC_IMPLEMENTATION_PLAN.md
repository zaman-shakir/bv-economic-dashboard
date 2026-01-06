# Invoice Sync Implementation Plan

## Problem Statement
- E-conomic has 21,500 invoices
- Currently fetching only last 6 months (~1,000 invoices)
- Fetching all 21.5k invoices at once causes page freeze

## Solution: Background Sync with Database Storage

### How It Works

```
┌─────────────────────────────────────────────────────────────┐
│ BACKGROUND SYNC (Every 2 hours)                            │
│                                                             │
│ 1. Fetch page 1 (1000 invoices) → Save to DB              │
│ 2. Fetch page 2 (1000 invoices) → Save to DB              │
│ 3. Fetch page 3 (1000 invoices) → Save to DB              │
│ ...                                                         │
│ 22. Fetch page 22 (500 invoices) → Save to DB             │
│                                                             │
│ Total time: 22-44 seconds (runs in background!)           │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ DASHBOARD (Instant - reads from DB)                        │
│                                                             │
│ 1. Query database for invoices                             │
│ 2. Filter by status (overdue/unpaid/all)                  │
│ 3. Group by employee                                        │
│ 4. Display results                                          │
│                                                             │
│ Load time: 50-200ms (instant!)                             │
└─────────────────────────────────────────────────────────────┘
```

## Implementation Steps

### Step 1: Database Schema

**Create `invoices` table:**

```sql
CREATE TABLE invoices (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    invoice_number INT UNIQUE NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    customer_number INT NOT NULL,
    customer_name VARCHAR(255),
    subject TEXT,
    gross_amount DECIMAL(15, 2),
    remainder DECIMAL(15, 2),
    currency VARCHAR(10),
    external_reference VARCHAR(255),
    employee_number INT,
    employee_name VARCHAR(255),
    pdf_url TEXT,
    raw_data JSON,  -- Store full API response
    last_synced_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    INDEX idx_invoice_number (invoice_number),
    INDEX idx_employee_number (employee_number),
    INDEX idx_customer_number (customer_number),
    INDEX idx_due_date (due_date),
    INDEX idx_remainder (remainder),
    INDEX idx_last_synced (last_synced_at)
);
```

### Step 2: Sync Process

**Service Method: `syncAllInvoices()`**

```php
public function syncAllInvoices(): array
{
    $stats = [
        'total_fetched' => 0,
        'total_created' => 0,
        'total_updated' => 0,
        'pages_processed' => 0,
        'errors' => [],
        'started_at' => now(),
    ];

    $pageNumber = 0;
    $pageSize = 1000;
    $hasMore = true;

    while ($hasMore) {
        try {
            // Fetch one page
            $url = "{$this->baseUrl}/invoices/booked";
            $url .= "?pagesize={$pageSize}&skippages={$pageNumber}";

            $response = Http::timeout(30)->withHeaders($this->headers)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $invoices = $data['collection'] ?? [];

                // Save to database
                foreach ($invoices as $invoice) {
                    $this->saveInvoiceToDatabase($invoice);
                    $stats['total_fetched']++;
                }

                $stats['pages_processed']++;

                // Check if more pages exist
                $pagination = $data['pagination'] ?? [];
                $hasMore = count($invoices) === $pageSize;
                $pageNumber++;

                // Sleep briefly to avoid rate limits
                usleep(100000); // 100ms between requests
            } else {
                $hasMore = false;
                $stats['errors'][] = "Page {$pageNumber} failed: " . $response->status();
            }
        } catch (\Exception $e) {
            $stats['errors'][] = "Page {$pageNumber} error: " . $e->getMessage();
            $hasMore = false;
        }
    }

    $stats['completed_at'] = now();
    $stats['duration'] = $stats['completed_at']->diffInSeconds($stats['started_at']);

    return $stats;
}
```

### Step 3: Artisan Command

**File: `app/Console/Commands/SyncInvoices.php`**

```php
php artisan make:command SyncInvoices

class SyncInvoices extends Command
{
    protected $signature = 'invoices:sync {--force}';
    protected $description = 'Sync all invoices from E-conomic to database';

    public function handle(EconomicInvoiceService $service)
    {
        $this->info('Starting invoice sync...');

        $stats = $service->syncAllInvoices();

        $this->info("Sync completed!");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Pages Processed', $stats['pages_processed']],
                ['Total Fetched', $stats['total_fetched']],
                ['Total Created', $stats['total_created']],
                ['Total Updated', $stats['total_updated']],
                ['Duration', $stats['duration'] . ' seconds'],
            ]
        );

        if (!empty($stats['errors'])) {
            $this->error('Errors occurred:');
            foreach ($stats['errors'] as $error) {
                $this->error($error);
            }
        }
    }
}
```

**Manual Trigger:**
```bash
php artisan invoices:sync
```

### Step 4: Scheduled Sync

**File: `app/Console/Kernel.php`**

```php
protected function schedule(Schedule $schedule)
{
    // Sync every 2 hours
    $schedule->command('invoices:sync')
             ->everyTwoHours()
             ->withoutOverlapping()
             ->runInBackground();
}
```

**For cPanel (no cron access):**
- Set up a webhook endpoint
- Use external service (like cron-job.org) to hit webhook every 2 hours

### Step 5: Dashboard Integration

**Update `DashboardController::index()`**

```php
public function index(Request $request)
{
    $filter = $request->get('filter', 'overdue');

    // Now reads from database instead of API
    $invoicesByEmployee = $this->invoiceService->getInvoicesByEmployeeFromDB($filter);
    $totals = $this->invoiceService->getInvoiceTotals();
    $dataQuality = $this->invoiceService->getDataQualityStats();

    // Add sync status
    $lastSyncedAt = Invoice::max('last_synced_at');

    return view('dashboard.index', [
        'invoicesByEmployee' => $invoicesByEmployee,
        'totals' => $totals,
        'dataQuality' => $dataQuality,
        'lastUpdated' => now()->format('d-m-Y H:i'),
        'currentFilter' => $filter,
        'lastSyncedAt' => $lastSyncedAt, // NEW
    ]);
}
```

### Step 6: "Sync Now" Button

**Add to `resources/views/dashboard/index.blade.php`**

```blade
<div class="flex items-center gap-3">
    <button
        onclick="syncNow()"
        class="px-5 py-2.5 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold rounded-xl transition-all duration-200 flex items-center gap-2 shadow-lg">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Sync Now (All 21.5k Invoices)
    </button>

    @if($lastSyncedAt)
        <span class="text-sm text-gray-600 dark:text-gray-400">
            Last synced: {{ $lastSyncedAt->diffForHumans() }}
        </span>
    @endif
</div>

<script>
function syncNow() {
    if (!confirm('This will sync all 21,500 invoices from E-conomic. It may take 30-60 seconds. Continue?')) {
        return;
    }

    const button = event.target.closest('button');
    button.disabled = true;
    button.innerHTML = '<svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Syncing...';

    fetch('/api/sync-invoices', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            alert(`Sync completed!\nFetched: ${data.total_fetched} invoices\nDuration: ${data.duration} seconds`);
            location.reload();
        })
        .catch(error => {
            alert('Sync failed: ' + error.message);
            button.disabled = false;
            button.innerHTML = 'Sync Now';
        });
}
</script>
```

### Step 7: API Endpoint for Sync

**File: `routes/api.php`**

```php
Route::post('/sync-invoices', [DashboardController::class, 'syncInvoices'])
    ->middleware('auth');
```

**File: `app/Http/Controllers/DashboardController.php`**

```php
public function syncInvoices(Request $request)
{
    set_time_limit(300); // 5 minutes max

    $stats = $this->invoiceService->syncAllInvoices();

    return response()->json($stats);
}
```

## Benefits

✅ **No Page Freeze**: Sync runs in background
✅ **Fast Dashboard**: Reads from local database (50-200ms)
✅ **All 21.5k Invoices**: Can handle unlimited invoices
✅ **Auto-Update**: Syncs every 2 hours automatically
✅ **Manual Control**: "Sync Now" button for immediate update
✅ **Historical Data**: Keep invoice history over time
✅ **Advanced Queries**: Can add complex filters, search, sorting

## Performance Comparison

| Method | Load Time | Handles 21.5k | Page Freeze |
|--------|-----------|---------------|-------------|
| **Current (API)** | 2-5 seconds | ❌ No (6 months only) | ⚠️ Yes (if >1k) |
| **New (Database)** | 50-200ms | ✅ Yes | ❌ No |

## Migration Path

1. **Phase 1**: Create database structure (Step 1)
2. **Phase 2**: Run initial sync manually (Step 3)
3. **Phase 3**: Update dashboard to read from DB (Step 5)
4. **Phase 4**: Add "Sync Now" button (Step 6)
5. **Phase 5**: Schedule automatic sync (Step 4)
6. **Phase 6**: Remove 6-month limit, show all data

## Rollback Plan

If issues occur:
1. Keep API-based code as fallback
2. Add environment variable: `USE_DATABASE_INVOICES=true/false`
3. Switch back to API if needed

## Timeline

- Setup (Steps 1-3): 1 hour
- Dashboard integration (Step 5): 30 minutes
- Sync button (Step 6-7): 30 minutes
- Testing: 30 minutes

**Total: ~2.5 hours**

---

Ready to implement? Let's start! 🚀
