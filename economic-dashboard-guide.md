# E-conomic Overdue Invoices Dashboard - Laravel Development Guide

## Project Overview

**Purpose**: Build a dashboard at `dash.billigventilation.dk` to display overdue/unpaid invoices from E-conomic, grouped by employee - a feature E-conomic has been missing for 10+ years.

**Tech Stack**: Laravel 11 + Blade + HTMX (optional for dynamic updates)

**SDK**: `morningtrain/laravel-economic` - the most actively maintained E-conomic SDK for Laravel

---

## Phase 1: Project Setup

### 1.1 Create Laravel Project

```bash
# Create new Laravel project
composer create-project laravel/laravel bv-dashboard

cd bv-dashboard

# Install the E-conomic SDK
composer require morningtrain/laravel-economic

# Publish the config file
php artisan vendor:publish --provider="Morningtrain\LaravelEconomic\LaravelEconomicServiceProvider"
```

### 1.2 Environment Configuration

Add to your `.env` file:

```env
# E-conomic API Credentials
ECONOMIC_APP_SECRET_TOKEN=your_app_secret_token_here
ECONOMIC_AGREEMENT_GRANT_TOKEN=your_agreement_grant_token_here

# For development/testing, use "demo" for both tokens (GET requests only)
# ECONOMIC_APP_SECRET_TOKEN=demo
# ECONOMIC_AGREEMENT_GRANT_TOKEN=demo
```

### 1.3 Config File

The published config file at `config/e-conomic.php`:

```php
<?php

return [
    'app_secret_token' => env('ECONOMIC_APP_SECRET_TOKEN'),
    'agreement_grant_token' => env('ECONOMIC_AGREEMENT_GRANT_TOKEN'),
    
    /*
     * Custom request logger (optional)
     * Useful for debugging API calls during development
     */
    'request_logger' => \Morningtrain\LaravelEconomic\RequestLogger\VoidRequestLogger::class,
];
```

---

## Phase 2: Understanding E-conomic Invoice Data

### 2.1 Key Invoice Endpoints

The E-conomic REST API provides these invoice-related endpoints:

| Endpoint | Description |
|----------|-------------|
| `GET /invoices/booked` | All booked invoices |
| `GET /invoices/booked/unpaid` | Outstanding invoices (remainder > 0) |
| `GET /invoices/booked/overdue` | Past due date with balance remaining |
| `GET /invoices/booked/paid` | Fully paid invoices |
| `GET /invoices/totals` | Aggregated statistics |

### 2.2 Invoice Data Structure (Mapping to Jesper's Fields)

```
E-conomic Field              → Dashboard Field (Danish)
─────────────────────────────────────────────────────────
customer.customerNumber      → Kundenr.
recipient.name               → Kundenavn
notes.heading                → Overskrift
grossAmount                  → Beløb
references.other             → Eksternt ID (WooCommerce order)
currency                     → Valuta (skip per Jesper)
project                      → Projekt (skip per Jesper)
dueDate                      → For calculating overdue status
remainder                    → Outstanding balance (0 = paid)
references.salesPerson       → For grouping by employee
```

### 2.3 Key Fields for Overdue Detection

- **`remainder`**: Amount still owed. If `remainder > 0`, invoice is unpaid
- **`dueDate`**: Due date in `YYYY-MM-DD` format
- **Overdue**: `remainder > 0 AND dueDate < today`

---

## Phase 3: Create the Invoice Service

### 3.1 Service Class

Create `app/Services/EconomicInvoiceService.php`:

```php
<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class EconomicInvoiceService
{
    protected string $baseUrl = 'https://restapi.e-conomic.com';
    protected array $headers;

    public function __construct()
    {
        $this->headers = [
            'X-AppSecretToken' => config('e-conomic.app_secret_token'),
            'X-AgreementGrantToken' => config('e-conomic.agreement_grant_token'),
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Get all overdue invoices
     */
    public function getOverdueInvoices(): Collection
    {
        return Cache::remember('overdue_invoices', 300, function () {
            $invoices = collect();
            $url = "{$this->baseUrl}/invoices/booked/overdue?pagesize=1000";

            while ($url) {
                $response = Http::withHeaders($this->headers)->get($url);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $invoices = $invoices->merge($data['collection'] ?? []);
                    $url = $data['pagination']['nextPage'] ?? null;
                } else {
                    break;
                }
            }

            return $invoices;
        });
    }

    /**
     * Get all unpaid invoices (includes not-yet-overdue)
     */
    public function getUnpaidInvoices(): Collection
    {
        return Cache::remember('unpaid_invoices', 300, function () {
            $invoices = collect();
            $url = "{$this->baseUrl}/invoices/booked/unpaid?pagesize=1000";

            while ($url) {
                $response = Http::withHeaders($this->headers)->get($url);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $invoices = $invoices->merge($data['collection'] ?? []);
                    $url = $data['pagination']['nextPage'] ?? null;
                } else {
                    break;
                }
            }

            return $invoices;
        });
    }

    /**
     * Get overdue invoices grouped by salesperson/employee
     */
    public function getOverdueByEmployee(): Collection
    {
        $invoices = $this->getOverdueInvoices();

        return $invoices->groupBy(function ($invoice) {
            return $invoice['references']['salesPerson']['employeeNumber'] ?? 'unassigned';
        })->map(function ($group, $employeeNumber) {
            return [
                'employeeNumber' => $employeeNumber,
                'employeeName' => $this->getEmployeeName($employeeNumber),
                'invoiceCount' => $group->count(),
                'totalAmount' => $group->sum('grossAmount'),
                'totalRemainder' => $group->sum('remainder'),
                'invoices' => $group->map(fn($inv) => $this->formatInvoice($inv)),
            ];
        });
    }

    /**
     * Get employee name by number
     */
    protected function getEmployeeName(int|string $employeeNumber): string
    {
        if ($employeeNumber === 'unassigned') {
            return 'Ikke tildelt';
        }

        return Cache::remember("employee_{$employeeNumber}", 3600, function () use ($employeeNumber) {
            $response = Http::withHeaders($this->headers)
                ->get("{$this->baseUrl}/employees/{$employeeNumber}");

            if ($response->successful()) {
                return $response->json()['name'] ?? "Medarbejder #{$employeeNumber}";
            }

            return "Medarbejder #{$employeeNumber}";
        });
    }

    /**
     * Format invoice for dashboard display
     */
    protected function formatInvoice(array $invoice): array
    {
        $dueDate = Carbon::parse($invoice['dueDate']);
        $daysOverdue = $dueDate->diffInDays(now());

        return [
            'invoiceNumber' => $invoice['bookedInvoiceNumber'],
            'kundenr' => $invoice['customer']['customerNumber'] ?? null,
            'kundenavn' => $invoice['recipient']['name'] ?? 'Ukendt kunde',
            'overskrift' => $invoice['notes']['heading'] ?? '',
            'beloeb' => $invoice['grossAmount'],
            'remainder' => $invoice['remainder'],
            'currency' => $invoice['currency'],
            'eksterntId' => $invoice['references']['other'] ?? null,
            'dueDate' => $invoice['dueDate'],
            'daysOverdue' => $daysOverdue,
            'pdfUrl' => $invoice['pdf']['download'] ?? null,
        ];
    }

    /**
     * Get invoice totals/statistics
     */
    public function getInvoiceTotals(): array
    {
        return Cache::remember('invoice_totals', 300, function () {
            $response = Http::withHeaders($this->headers)
                ->get("{$this->baseUrl}/invoices/totals");

            return $response->successful() ? $response->json() : [];
        });
    }

    /**
     * Clear all cached data
     */
    public function clearCache(): void
    {
        Cache::forget('overdue_invoices');
        Cache::forget('unpaid_invoices');
        Cache::forget('invoice_totals');
    }
}
```

### 3.2 Register Service Provider

Add to `app/Providers/AppServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\EconomicInvoiceService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EconomicInvoiceService::class);
    }

    public function boot(): void
    {
        //
    }
}
```

---

## Phase 4: Create Controllers

### 4.1 Dashboard Controller

Create `app/Http/Controllers/DashboardController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Services\EconomicInvoiceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected EconomicInvoiceService $invoiceService
    ) {}

    /**
     * Main dashboard view - overdue invoices grouped by employee
     */
    public function index(): View
    {
        $overdueByEmployee = $this->invoiceService->getOverdueByEmployee();
        $totals = $this->invoiceService->getInvoiceTotals();

        return view('dashboard.index', [
            'overdueByEmployee' => $overdueByEmployee,
            'totals' => $totals,
            'lastUpdated' => now()->format('d-m-Y H:i'),
        ]);
    }

    /**
     * HTMX partial - refresh invoice list
     */
    public function refreshInvoices(): View
    {
        $this->invoiceService->clearCache();
        $overdueByEmployee = $this->invoiceService->getOverdueByEmployee();

        return view('dashboard.partials.invoice-list', [
            'overdueByEmployee' => $overdueByEmployee,
        ]);
    }

    /**
     * API endpoint for future integrations
     */
    public function apiOverdue(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => $this->invoiceService->getOverdueByEmployee(),
            'meta' => [
                'fetched_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
```

---

## Phase 5: Create Routes

### 5.1 Web Routes

Add to `routes/web.php`:

```php
<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/refresh', [DashboardController::class, 'refreshInvoices'])->name('dashboard.refresh');

// API routes for future integrations
Route::prefix('api')->group(function () {
    Route::get('/overdue', [DashboardController::class, 'apiOverdue']);
});
```

---

## Phase 6: Create Blade Views

### 6.1 Layout

Create `resources/views/layouts/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Faktura Dashboard') - BilligVentilation</title>
    
    <!-- Tailwind CSS via CDN (or use Vite for production) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- HTMX for dynamic updates without JavaScript -->
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-800">
                    BilligVentilation - Faktura Overblik
                </h1>
                <span class="text-sm text-gray-500">
                    Sidst opdateret: {{ $lastUpdated ?? now()->format('d-m-Y H:i') }}
                </span>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-6">
        @yield('content')
    </main>

    <footer class="bg-white border-t mt-auto">
        <div class="max-w-7xl mx-auto px-4 py-4 text-center text-sm text-gray-500">
            E-conomic Integration Dashboard v1.0
        </div>
    </footer>
</body>
</html>
```

### 6.2 Dashboard Index

Create `resources/views/dashboard/index.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Forfaldne Fakturaer')

@section('content')
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Forfaldne Fakturaer</h3>
            <p class="text-2xl font-bold text-red-600">
                {{ $overdueByEmployee->sum('invoiceCount') }}
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Total Udestående</h3>
            <p class="text-2xl font-bold text-gray-900">
                {{ number_format($overdueByEmployee->sum('totalRemainder'), 2, ',', '.') }} DKK
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Medarbejdere med Forfaldne</h3>
            <p class="text-2xl font-bold text-gray-900">
                {{ $overdueByEmployee->count() }}
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <button 
                hx-get="{{ route('dashboard.refresh') }}"
                hx-target="#invoice-list"
                hx-swap="innerHTML"
                hx-indicator="#loading"
                class="w-full bg-blue-600 text-white rounded py-2 px-4 hover:bg-blue-700 transition"
            >
                🔄 Opdater Data
            </button>
            <div id="loading" class="htmx-indicator text-center mt-2 text-sm text-gray-500">
                Henter data...
            </div>
        </div>
    </div>

    <!-- Invoice List by Employee -->
    <div id="invoice-list">
        @include('dashboard.partials.invoice-list', ['overdueByEmployee' => $overdueByEmployee])
    </div>
@endsection
```

### 6.3 Invoice List Partial (for HTMX)

Create `resources/views/dashboard/partials/invoice-list.blade.php`:

```blade
@forelse($overdueByEmployee as $employeeData)
    <div class="bg-white rounded-lg shadow mb-6">
        <!-- Employee Header -->
        <div class="bg-gray-50 px-4 py-3 border-b rounded-t-lg flex justify-between items-center">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">
                    {{ $employeeData['employeeName'] }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ $employeeData['invoiceCount'] }} forfaldne fakturaer
                </p>
            </div>
            <div class="text-right">
                <p class="text-lg font-bold text-red-600">
                    {{ number_format($employeeData['totalRemainder'], 2, ',', '.') }} DKK
                </p>
                <p class="text-xs text-gray-500">udestående</p>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Kundenr.</th>
                        <th class="px-4 py-2 text-left">Kundenavn</th>
                        <th class="px-4 py-2 text-left">Overskrift</th>
                        <th class="px-4 py-2 text-right">Beløb</th>
                        <th class="px-4 py-2 text-right">Udestående</th>
                        <th class="px-4 py-2 text-center">Dage Forfalden</th>
                        <th class="px-4 py-2 text-left">Eksternt ID</th>
                        {{-- Future: Reminder button --}}
                        {{-- <th class="px-4 py-2 text-center">Handling</th> --}}
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($employeeData['invoices'] as $invoice)
                        <tr class="hover:bg-gray-50 {{ $invoice['daysOverdue'] > 30 ? 'bg-red-50' : ($invoice['daysOverdue'] > 14 ? 'bg-yellow-50' : '') }}">
                            <td class="px-4 py-3 text-sm">
                                {{ $invoice['kundenr'] }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium">
                                {{ $invoice['kundenavn'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ Str::limit($invoice['overskrift'], 40) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                {{ number_format($invoice['beloeb'], 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-red-600">
                                {{ number_format($invoice['remainder'], 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    {{ $invoice['daysOverdue'] > 30 ? 'bg-red-100 text-red-800' : 
                                       ($invoice['daysOverdue'] > 14 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ $invoice['daysOverdue'] }} dage
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if($invoice['eksterntId'])
                                    <span class="font-mono text-xs">{{ $invoice['eksterntId'] }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            {{-- Future: Reminder button
                            <td class="px-4 py-3 text-center">
                                <button class="text-blue-600 hover:text-blue-800 text-sm">
                                    📧 Rykker
                                </button>
                            </td>
                            --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="bg-green-50 border border-green-200 rounded-lg p-8 text-center">
        <span class="text-4xl">🎉</span>
        <h3 class="mt-2 text-lg font-medium text-green-800">Ingen forfaldne fakturaer!</h3>
        <p class="text-green-600">Alle fakturaer er betalt til tiden.</p>
    </div>
@endforelse
```

---

## Phase 7: Database (Optional - For Local Caching)

If you want to store invoice snapshots locally for historical tracking:

### 7.1 Migration

```bash
php artisan make:migration create_invoice_snapshots_table
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('source')->default('e-conomic'); // For future integrations
            $table->integer('invoice_number');
            $table->integer('customer_number')->nullable();
            $table->string('customer_name');
            $table->string('heading')->nullable();
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('remainder', 12, 2);
            $table->string('currency', 3)->default('DKK');
            $table->string('external_id')->nullable(); // WooCommerce order
            $table->date('due_date');
            $table->integer('employee_number')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
            
            $table->index(['source', 'invoice_number']);
            $table->index('employee_number');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_snapshots');
    }
};
```

---

## Phase 8: Testing Setup

### 8.1 Feature Test

Create `tests/Feature/DashboardTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class DashboardTest extends TestCase
{
    public function test_dashboard_loads(): void
    {
        // Mock the E-conomic API response
        Http::fake([
            'restapi.e-conomic.com/*' => Http::response([
                'collection' => [],
                'pagination' => ['nextPage' => null]
            ], 200),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('BilligVentilation');
    }
}
```

---

## Phase 9: Deployment

### 9.1 Server Requirements

- PHP 8.2+
- Composer
- Laravel 11
- SSL certificate (required for E-conomic API)

### 9.2 Production Checklist

```bash
# On server
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 9.3 Scheduled Cache Refresh (Optional)

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Refresh invoice cache every 15 minutes during business hours
    $schedule->call(function () {
        app(EconomicInvoiceService::class)->clearCache();
        app(EconomicInvoiceService::class)->getOverdueByEmployee();
    })->weekdays()
      ->between('7:00', '18:00')
      ->everyFifteenMinutes();
}
```

---

## Phase 10: Future Enhancements

### 10.1 Reminder Email Feature (v2)

Jesper mentioned wanting buttons to send reminder emails. Here's the structure:

```php
// app/Services/ReminderService.php
class ReminderService
{
    public function sendReminder(int $invoiceNumber): bool
    {
        // 1. Fetch invoice details from E-conomic
        // 2. Get customer email from E-conomic customer endpoint
        // 3. Generate PDF from E-conomic (invoice has pdf.download URL)
        // 4. Send email via Laravel Mail
        // 5. Log reminder in database
    }
}
```

### 10.2 Generic Integration Architecture

Since Jesper wants to add more integrations later, structure the service like this:

```php
// app/Contracts/InvoiceProvider.php
interface InvoiceProvider
{
    public function getOverdueInvoices(): Collection;
    public function getUnpaidInvoices(): Collection;
    public function getInvoiceDetails(string $id): array;
}

// app/Services/Providers/EconomicProvider.php
class EconomicProvider implements InvoiceProvider { ... }

// app/Services/Providers/DineroProvider.php (future)
class DineroProvider implements InvoiceProvider { ... }
```

---

## Quick Reference: E-conomic API

### Authentication Headers

```
X-AppSecretToken: your_app_secret_token
X-AgreementGrantToken: your_agreement_grant_token
Content-Type: application/json
```

### Useful Endpoints

| Endpoint | Purpose |
|----------|---------|
| `GET /invoices/booked/overdue` | Overdue invoices |
| `GET /invoices/booked/unpaid` | All unpaid |
| `GET /invoices/booked/{number}` | Single invoice details |
| `GET /customers/{number}` | Customer details |
| `GET /employees/{number}` | Employee details |
| `GET /invoices/totals` | Statistics |

### Filtering Syntax

```
?filter=dueDate$lt:2025-01-01
?filter=remainder$gt:0$and:currency$eq:DKK
?filter=customer.customerNumber$eq:1234
```

### Documentation Links

- REST API Docs: https://restdocs.e-conomic.com/
- Developer Portal: https://www.e-conomic.com/developer
- SDK GitHub: https://github.com/Morning-Train/laravel-economic

---

## Getting Started Checklist

- [ ] Create Laravel project
- [ ] Install `morningtrain/laravel-economic`
- [ ] Configure `.env` with API tokens
- [ ] Create `EconomicInvoiceService`
- [ ] Create `DashboardController`
- [ ] Create Blade views
- [ ] Test with demo tokens
- [ ] Deploy to `dash.billigventilation.dk`
- [ ] Configure real API tokens from BilligVentilation
- [ ] Set up cache refresh schedule

---

## Notes for Shakir

1. **The SDK limitation**: The `morningtrain/economic` SDK doesn't have pre-built classes for the `/invoices/booked/overdue` endpoint specifically. You'll need to use Laravel's HTTP client directly (as shown in the service class above) or extend the SDK.

2. **HTMX is optional**: I included it because it's incredibly simple - just HTML attributes, no JavaScript to write. But you can remove it and use plain Blade if you prefer.

3. **Demo mode**: Use `demo` for both tokens during development. Note: demo mode only works for GET requests.

4. **Rate limits**: E-conomic doesn't publish strict rate limits but has a "fair usage policy". The 5-minute cache in the service class helps with this.

5. **Employee grouping**: The `references.salesPerson` field links invoices to employees. If this isn't populated for some invoices, they'll appear under "Ikke tildelt".
