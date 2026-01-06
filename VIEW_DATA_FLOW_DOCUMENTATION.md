# BV Economic Dashboard - View-by-View Data Flow

## Table of Contents
1. [Dashboard Main View](#dashboard-main-view)
2. [Dashboard Partial - Invoice List](#dashboard-partial---invoice-list)
3. [Statistics View](#statistics-view)
4. [Reminders View](#reminders-view)
5. [User Management View](#user-management-view)
6. [Data Flow Summary](#data-flow-summary)

---

## Dashboard Main View

**File**: `resources/views/dashboard/index.blade.php`

**Route**: `GET /dashboard`

**Controller**: `DashboardController::index()`

**Code Reference**: `app/Http/Controllers/DashboardController.php:18-39`

### Data Received

```php
// From DashboardController::index()
return view('dashboard.index', [
    'invoicesByEmployee' => $invoicesByEmployee,  // Collection of grouped invoices
    'totals' => $totals,                          // E-conomic totals
    'dataQuality' => $dataQuality,                // Data quality stats
    'lastUpdated' => now()->format('d-m-Y H:i'),  // String: "06-01-2026 14:30"
    'currentFilter' => $filter,                    // String: 'all', 'overdue', or 'unpaid'
]);
```

### Real Data Example

#### 1. `$invoicesByEmployee` Structure

```php
Collection {
    "3" => [
        "employeeNumber" => 3,
        "employeeName" => "Jesper Nielsen",
        "invoiceCount" => 3,
        "totalAmount" => 85000.00,
        "totalRemainder" => 85000.00,
        "invoices" => Collection [
            [
                "invoiceNumber" => 10003,
                "kundenr" => 1003,
                "kundenavn" => "Office Solutions Denmark",
                "overskrift" => "HVAC system installation - Building 3",
                "beloeb" => 45000.00,
                "remainder" => 45000.00,
                "currency" => "DKK",
                "eksterntId" => null,
                "date" => "2025-10-10",
                "dueDate" => "2025-11-01",
                "daysOverdue" => 66,
                "daysTillDue" => 0,
                "status" => "overdue",
                "pdfUrl" => "https://example.com/invoice.pdf"
            ],
            [
                "invoiceNumber" => 10001,
                "kundenr" => 1001,
                "kundenavn" => "Restaurant Nordic A/S",
                "overskrift" => "Order 2547 - Industrial ventilation system",
                "beloeb" => 25000.00,
                "remainder" => 25000.00,
                "currency" => "DKK",
                "eksterntId" => "WC-2547",
                "date" => "2025-11-15",
                "dueDate" => "2025-12-01",
                "daysOverdue" => 36,
                "daysTillDue" => 0,
                "status" => "overdue",
                "pdfUrl" => "https://example.com/invoice.pdf"
            ],
            [
                "invoiceNumber" => 10002,
                "kundenr" => 1002,
                "kundenavn" => "Copenhagen Hotels ApS",
                "overskrift" => "Order 2589 - Kitchen exhaust fan replacement",
                "beloeb" => 15000.00,
                "remainder" => 15000.00,
                "currency" => "DKK",
                "eksterntId" => "WC-2589",
                "date" => "2025-11-20",
                "dueDate" => "2025-12-05",
                "daysOverdue" => 32,
                "daysTillDue" => 0,
                "status" => "overdue",
                "pdfUrl" => "https://example.com/invoice.pdf"
            ]
        ]
    ],
    "7" => [
        "employeeNumber" => 7,
        "employeeName" => "Maria Hansen",
        "invoiceCount" => 2,
        "totalAmount" => 20500.00,
        "totalRemainder" => 20500.00,
        "invoices" => Collection [
            // ... similar invoice structure
        ]
    ],
    "unassigned" => [
        "employeeNumber" => "unassigned",
        "employeeName" => "Unassigned",
        "invoiceCount" => 1,
        "totalAmount" => 9800.00,
        "totalRemainder" => 9800.00,
        "invoices" => Collection [
            // ... similar invoice structure
        ]
    ]
}
```

#### 2. `$totals` Structure (from E-conomic API)

```php
[
    "drafts" => 12,
    "booked" => 1547,
    "overdue" => 23,
    "notDue" => 45,
    "paid" => 1479
]
```

#### 3. `$dataQuality` Structure

```php
[
    "has_unassigned" => true,
    "unassigned_count" => 15,
    "total_count" => 127,
    "percentage" => 12,
    "message" => "15 out of 127 invoices (12%) have no salesperson assigned.",
    "suggestion" => "Assign salespeople to invoices in your e-conomic dashboard for better tracking."
]
```

### How Data is Used in View

#### Top Toolbar - Filter Buttons (Lines 10-24)

```html
<!-- Active filter highlighted with gradient -->
<a href="{{ route('dashboard', ['filter' => 'overdue']) }}"
   class="{{ $currentFilter === 'overdue' ? 'bg-gradient-to-r from-red-600 to-red-700 text-white' : 'bg-white text-gray-700' }}">
    Overdue Only
</a>
```

**Result**: When `$currentFilter = 'overdue'`, button shows red gradient background.

#### Employee Filter Dropdown (Lines 26-37)

```html
<select id="employeeFilter">
    <option value="">All Employees</option>
    @foreach($invoicesByEmployee as $emp)
        <option value="{{ $emp['employeeNumber'] }}">
            {{ $emp['employeeName'] }} ({{ $emp['invoiceCount'] }})
        </option>
    @endforeach
</select>
```

**Rendered HTML**:
```html
<option value="3">Jesper Nielsen (3)</option>
<option value="7">Maria Hansen (2)</option>
<option value="unassigned">Unassigned (1)</option>
```

#### Data Info Banner (Lines 128-149)

```html
<span>Last {{ config('e-conomic.sync_months', 6) }} months</span>
<span>Total: <strong>{{ $invoicesByEmployee->sum('invoiceCount') }}</strong></span>

@if(isset($dataQuality) && $dataQuality['has_unassigned'])
    <div class="text-yellow-700">
        ⚠️ {{ $dataQuality['message'] }}
    </div>
@endif
```

**Rendered Output**:
```
Last 6 months (Jul 06, 2025 - Jan 06, 2026)
Total: 6 invoices
⚠️ 15 out of 127 invoices (12%) have no salesperson assigned.
```

#### Invoice List Inclusion (Line 154)

```php
@include('dashboard.partials.invoice-list', [
    'invoicesByEmployee' => $invoicesByEmployee,
    'currentFilter' => $currentFilter
])
```

This passes data to the partial view for rendering the main invoice table.

---

## Dashboard Partial - Invoice List

**File**: `resources/views/dashboard/partials/invoice-list.blade.php`

**Included By**: `dashboard/index.blade.php` and returned by HTMX refresh

**Controller**: `DashboardController::refreshInvoices()` (for HTMX)

**Code Reference**: `app/Http/Controllers/DashboardController.php:44-54`

### Data Received

Same as main dashboard:
- `$invoicesByEmployee` - Collection of employees with their invoices
- `$currentFilter` - Current filter mode

### How Data is Used

#### Loop Through Employees (Line 1)

```php
@forelse($invoicesByEmployee as $employeeData)
```

Each iteration processes one employee group (e.g., "Jesper Nielsen" with 3 invoices).

#### Employee Header Section (Lines 5-48)

```php
<div data-employee-section="{{ $employeeData['employeeNumber'] }}">
    <h2>{{ $employeeData['employeeName'] }}</h2>
    <p>{{ $employeeData['invoiceCount'] }} invoices</p>

    @if($criticalInvoices > 0)
        <span class="bg-red-100">
            🚨 {{ $criticalInvoices }} Critical
        </span>
    @endif

    <p class="text-red-600">
        {{ number_format($employeeData['totalRemainder'], 2, ',', '.') }} DKK
    </p>
</div>
```

**Rendered HTML** (for Jesper Nielsen):
```html
<div data-employee-section="3">
    <h2>Jesper Nielsen</h2>
    <p>3 invoices</p>
    <span class="bg-red-100">🚨 2 Critical</span>
    <p class="text-red-600">85.000,00 DKK</p>
</div>
```

#### Critical Invoices Calculation (Lines 2-4)

```php
@php
    $criticalInvoices = collect($employeeData['invoices'])
        ->filter(fn($inv) => $inv['daysOverdue'] > 30)
        ->count();
@endphp
```

**Example**: If Jesper has invoices with 66 days and 36 days overdue, `$criticalInvoices = 2`.

#### Invoice Table (Lines 55-154)

Each invoice row displays:

```php
@foreach($employeeData['invoices'] as $invoice)
    <tr class="{{
        $invoice['status'] === 'overdue' && $invoice['daysOverdue'] > 30
            ? 'bg-red-50'
            : ($invoice['status'] === 'overdue' && $invoice['daysOverdue'] > 14
                ? 'bg-yellow-50'
                : '')
    }}">
```

**Color Coding Logic**:
- **Red background**: `daysOverdue > 30` (Critical)
- **Yellow background**: `daysOverdue > 14` (Warning)
- **White background**: `daysOverdue ≤ 14` (Normal)

**Example Row** (Invoice #10003):
```html
<tr class="bg-red-50"> <!-- 66 days overdue = red background -->
    <td>📄 10003</td>
    <td>10.10.25</td>
    <td>1003</td>
    <td>Office Solutions Denmark</td>
    <td>HVAC system installation - Building 3</td>
    <td>45.000,00</td>
    <td class="text-red-600">45.000,00</td>
    <td>DKK</td>
    <td>
        <span class="bg-red-100 text-red-800">66 days overdue</span>
    </td>
    <td>-</td>
    <td>
        <button onclick="sendReminder(10003, 1003, this)">
            📧 Email
        </button>
    </td>
</tr>
```

#### Status Badge Rendering (Lines 116-130)

```php
@if($invoice['status'] === 'paid')
    <span class="bg-green-100">Paid</span>
@elseif($invoice['status'] === 'overdue')
    <span class="{{
        $invoice['daysOverdue'] > 30 ? 'bg-red-100 text-red-800' :
        ($invoice['daysOverdue'] > 14 ? 'bg-yellow-100 text-yellow-800' : 'bg-orange-100 text-orange-800')
    }}">
        {{ $invoice['daysOverdue'] }} days overdue
    </span>
@else
    <span class="bg-blue-100">{{ $invoice['daysTillDue'] }} days remaining</span>
@endif
```

**Real Examples**:
- Invoice with `remainder = 0` → Green "Paid" badge
- Invoice with `daysOverdue = 66` → Red "66 days overdue" badge
- Invoice with `daysOverdue = 18` → Yellow "18 days overdue" badge
- Invoice with `daysTillDue = 5` → Blue "5 days remaining" badge

#### Empty State (Lines 158-163)

```php
@empty
    <div class="bg-green-50">
        <span>🎉</span>
        <h3>No overdue invoices</h3>
        <p>All invoices are paid or on time</p>
    </div>
@endforelse
```

Shows when `$invoicesByEmployee` is empty (all invoices paid).

---

## Statistics View

**File**: `resources/views/dashboard/stats.blade.php`

**Route**: `GET /dashboard/stats`

**Controller**: `DashboardController::stats()`

**Code Reference**: `app/Http/Controllers/DashboardController.php:72-84`

### Data Received

```php
return view('dashboard.stats', [
    'invoicesByEmployee' => $invoicesByEmployee,  // Same as main dashboard
    'totals' => $totals,                          // E-conomic totals
    'lastUpdated' => now()->format('d-m-Y H:i'),  // "06-01-2026 14:30"
    'currentFilter' => $filter,                    // 'all', 'overdue', or 'unpaid'
]);
```

### How Data is Used

#### Quick Stats Card (Lines 34-66)

```php
<div>
    <p>
        @if($currentFilter === 'all')
            Total Invoices
        @elseif($currentFilter === 'unpaid')
            Unpaid Invoices
        @else
            Overdue Invoices
        @endif
    </p>
    <p class="text-3xl">{{ $invoicesByEmployee->sum('invoiceCount') }}</p>
</div>

<div>
    <p>Total Outstanding</p>
    <p class="text-2xl">
        {{ number_format($invoicesByEmployee->sum('totalRemainder'), 2, ',', '.') }} DKK
    </p>
</div>

<div>
    <p>Employees Count</p>
    <p class="text-2xl">{{ $invoicesByEmployee->count() }}</p>
</div>
```

**Real Output** (overdue filter):
```
Overdue Invoices
6

Total Outstanding
115.300,00 DKK

Employees Count
3
```

#### Critical Invoices Card (Lines 68-101)

```php
@php
    $criticalCount = 0;
    $criticalAmount = 0;
    foreach($invoicesByEmployee as $emp) {
        foreach($emp['invoices'] as $inv) {
            if(isset($inv['daysOverdue']) && $inv['daysOverdue'] > 30) {
                $criticalCount++;
                $criticalAmount += $inv['remainder'];
            }
        }
    }
@endphp

@if($criticalCount > 0)
    <div class="bg-red-50 border-red-200">
        <h3>🚨 Critical Invoices</h3>
        <p>Over 30 Days</p>
        <p class="text-3xl">{{ $criticalCount }}</p>

        <p>Critical Amount</p>
        <p class="text-xl">{{ number_format($criticalAmount, 2, ',', '.') }} DKK</p>
    </div>
@endif
```

**Real Calculation**:
- Jesper: Invoice #10003 (66 days, 45,000 DKK) + #10001 (36 days, 25,000 DKK)
- Maria: Invoice #10005 (47 days, 8,500 DKK)
- **Result**: `$criticalCount = 3`, `$criticalAmount = 78,500 DKK`

#### Top Employees List (Lines 104-125)

```php
<h3>👥 Top Employees</h3>
<div>
    @foreach($invoicesByEmployee->sortByDesc('totalRemainder')->take(5) as $emp)
        <div>
            <p>{{ $emp['employeeName'] }}</p>
            <p>{{ $emp['invoiceCount'] }} invoices</p>
            <p class="text-red-600">
                {{ number_format($emp['totalRemainder'], 0, ',', '.') }}
            </p>
        </div>
    @endforeach
</div>
```

**Rendered List** (sorted by `totalRemainder` descending):
```
1. Jesper Nielsen - 3 invoices - 85.000 DKK
2. Maria Hansen - 2 invoices - 20.500 DKK
3. Unassigned - 1 invoice - 9.800 DKK
```

#### Chart Data Preparation (Lines 157-166)

```php
@php
    $statusCounts = ['overdue' => 0, 'paid' => 0, 'unpaid' => 0];
    foreach($invoicesByEmployee as $emp) {
        foreach($emp['invoices'] as $inv) {
            $statusCounts[$inv['status']]++;
        }
    }

    $topEmployees = $invoicesByEmployee->sortByDesc('totalRemainder')->take(5)->values();
@endphp
```

**Real Data**:
```php
$statusCounts = [
    'overdue' => 6,
    'paid' => 0,
    'unpaid' => 0
];
```

#### Chart.js - Status Distribution (Lines 169-204)

```javascript
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Overdue', 'Paid', 'Unpaid'],
        datasets: [{
            data: [6, 0, 0],  // From $statusCounts
            backgroundColor: [
                'rgba(239, 68, 68, 0.8)',   // Red for overdue
                'rgba(34, 197, 94, 0.8)',   // Green for paid
                'rgba(59, 130, 246, 0.8)'   // Blue for unpaid
            ]
        }]
    }
});
```

**Chart Output**: Doughnut chart showing 100% red (all overdue).

#### Chart.js - Employee Bar Chart (Lines 207-262)

```javascript
new Chart(employeeCtx, {
    type: 'bar',
    data: {
        labels: ['Jesper Nielsen', 'Maria Hansen', 'Unassigned'],
        datasets: [{
            label: 'Outstanding (DKK)',
            data: [85000.00, 20500.00, 9800.00],
            backgroundColor: 'rgba(239, 68, 68, 0.7)'
        }]
    }
});
```

**Chart Output**: Bar chart with 3 bars showing outstanding amounts per employee.

---

## Reminders View

**File**: `resources/views/reminders/index.blade.php`

**Route**: `GET /reminders`

**Controller**: `ReminderController::index()`

**Code Reference**: `app/Http/Controllers/ReminderController.php:24-42`

### Data Received

```php
return view('reminders.index', [
    'reminders' => $reminders,  // Paginated collection (50 per page)
    'stats' => $stats,          // Statistics array
]);
```

### Real Data Examples

#### `$stats` Structure

```php
[
    'total' => 127,        // Total reminders in database
    'sent' => 119,         // Successfully sent (email_sent = true)
    'failed' => 8,         // Failed to send (email_sent = false)
    'today' => 12,         // Sent today
    'this_week' => 45,     // Sent this week
]
```

#### `$reminders` Structure (paginated)

```php
LengthAwarePaginator {
    "current_page": 1,
    "data": [
        InvoiceReminder {
            "id": 15,
            "invoice_number": 10001,
            "customer_email": "contact@restaurant-nordic.dk",
            "customer_name": "Restaurant Nordic A/S",
            "amount_due": 25000.00,
            "email_sent": true,
            "email_error": null,
            "sent_by": 1,
            "created_at": "2026-01-05 10:30:00",
            "sentBy": User {
                "id": 1,
                "name": "Admin User"
            }
        },
        InvoiceReminder {
            "id": 14,
            "invoice_number": 10002,
            "customer_email": "unknown@example.com",
            "customer_name": "Copenhagen Hotels ApS",
            "amount_due": 15000.00,
            "email_sent": false,
            "email_error": "Connection timeout",
            "sent_by": 1,
            "created_at": "2026-01-05 09:15:00",
            "sentBy": User {
                "id": 1,
                "name": "Admin User"
            }
        }
    ],
    "per_page": 50,
    "total": 127
}
```

### How Data is Used

#### Stats Cards (Lines 12-33)

```php
<div class="bg-white">
    <p>Total Reminders</p>
    <p class="text-3xl">{{ $stats['total'] }}</p>
</div>

<div class="bg-green-50">
    <p>Successfully Sent</p>
    <p class="text-3xl text-green-600">{{ $stats['sent'] }}</p>
</div>

<div class="bg-red-50">
    <p>Failed</p>
    <p class="text-3xl text-red-600">{{ $stats['failed'] }}</p>
</div>

<div class="bg-blue-50">
    <p>Today</p>
    <p class="text-3xl text-blue-600">{{ $stats['today'] }}</p>
</div>

<div class="bg-purple-50">
    <p>This Week</p>
    <p class="text-3xl text-purple-600">{{ $stats['this_week'] }}</p>
</div>
```

**Rendered Output**:
```
[Total: 127] [Sent: 119] [Failed: 8] [Today: 12] [Week: 45]
```

#### Reminders Table (Lines 42-96)

```php
@forelse($reminders as $reminder)
    <tr>
        <td>{{ $reminder->created_at->format('d-m-Y H:i') }}</td>
        <td>#{{ $reminder->invoice_number }}</td>
        <td>{{ $reminder->customer_name }}</td>
        <td>{{ $reminder->customer_email }}</td>
        <td>{{ number_format($reminder->amount_due, 2, ',', '.') }} DKK</td>
        <td>
            @if($reminder->email_sent)
                <span class="bg-green-100">✓ Sent</span>
            @else
                <span class="bg-red-100" title="{{ $reminder->email_error }}">
                    ✗ Failed
                </span>
            @endif
        </td>
        <td>{{ $reminder->sentBy->name ?? 'System' }}</td>
    </tr>
@empty
    <tr>
        <td colspan="7">No reminders sent yet</td>
    </tr>
@endforelse
```

**Real Table Rows**:
```
| Date/Time        | Invoice | Customer                  | Email                           | Amount Due     | Status       | Sent By    |
|------------------|---------|---------------------------|---------------------------------|----------------|--------------|------------|
| 05-01-2026 10:30 | #10001  | Restaurant Nordic A/S     | contact@restaurant-nordic.dk    | 25.000,00 DKK  | ✓ Sent       | Admin User |
| 05-01-2026 09:15 | #10002  | Copenhagen Hotels ApS     | unknown@example.com             | 15.000,00 DKK  | ✗ Failed     | Admin User |
```

#### Pagination (Lines 100-104)

```php
@if($reminders->hasPages())
    <div>
        {{ $reminders->links() }}
    </div>
@endif
```

**When Active**: Shows "Previous | 1 2 3 ... 10 | Next" if more than 50 reminders exist.

---

## User Management View

**File**: `resources/views/users/index.blade.php`

**Route**: `GET /users` (Admin only)

**Controller**: `UserController::index()`

**Code Reference**: `app/Http/Controllers/UserController.php`

**Middleware**: `auth, admin`

### Data Received

```php
return view('users.index', [
    'users' => User::all(),  // Collection of all users
]);
```

### Real Data Example

```php
Collection [
    User {
        "id": 1,
        "name": "Admin User",
        "email": "admin@billigventilation.dk",
        "is_admin": true,
        "created_at": "2025-12-01 08:00:00"
    },
    User {
        "id": 2,
        "name": "Jesper Nielsen",
        "email": "jesper@billigventilation.dk",
        "is_admin": false,
        "created_at": "2025-12-15 10:30:00"
    },
    User {
        "id": 3,
        "name": "Maria Hansen",
        "email": "maria@billigventilation.dk",
        "is_admin": false,
        "created_at": "2026-01-02 14:20:00"
    }
]
```

### How Data is Used

#### Success/Error Messages (Lines 16-26)

```php
@if(session('success'))
    <div class="bg-green-50">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-50">
        {{ session('error') }}
    </div>
@endif
```

**Example**: After creating a user, session contains:
```php
session('success') = "User 'Maria Hansen' created successfully"
```

#### Users Table Loop (Lines 42-81)

```php
@foreach($users as $user)
    <tr>
        <td>
            {{ $user->name }}
            @if($user->id === auth()->id())
                <span>(You)</span>
            @endif
        </td>
        <td>{{ $user->email }}</td>
        <td>
            @if($user->is_admin)
                <span class="bg-blue-100">Admin</span>
            @else
                <span class="bg-gray-100">User</span>
            @endif
        </td>
        <td>{{ $user->created_at->format('d M Y') }}</td>
        <td>
            @if($user->id !== auth()->id())
                <form action="{{ route('users.destroy', $user) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            @else
                <span>-</span>
            @endif
        </td>
    </tr>
@endforeach
```

**Rendered Table** (logged in as Admin User, id=1):
```
| Name                 | Email                          | Admin | Created     | Actions |
|----------------------|--------------------------------|-------|-------------|---------|
| Admin User (You)     | admin@billigventilation.dk    | Admin | 01 Dec 2025 | -       |
| Jesper Nielsen       | jesper@billigventilation.dk   | User  | 15 Dec 2025 | Delete  |
| Maria Hansen         | maria@billigventilation.dk    | User  | 02 Jan 2026 | Delete  |
```

**Security Logic**:
- Current user sees "(You)" label
- Current user cannot delete themselves
- Only admins can access this page

---

## Data Flow Summary

### Complete Data Journey

```
┌─────────────────────────────────────────────────────────────┐
│ 1. USER REQUEST                                             │
│    GET /dashboard?filter=overdue                            │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. CONTROLLER (DashboardController::index)                  │
│    - Receives filter parameter                              │
│    - Calls service methods                                  │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. SERVICE (EconomicInvoiceService)                         │
│    ┌──────────────────────────────────────────────┐        │
│    │ getInvoicesByEmployee('overdue')             │        │
│    └──────────────┬───────────────────────────────┘        │
│                   │                                         │
│    ┌──────────────▼───────────────────────────────┐        │
│    │ Check Cache: 'overdue_invoices' (30 min)    │        │
│    └──────────────┬───────────────────────────────┘        │
│                   │                                         │
│         ┌─────────┴─────────┐                              │
│         │ Cache Hit?        │                              │
│         └─────┬───────┬─────┘                              │
│              YES      NO                                   │
│               │       │                                    │
│               │       ▼                                    │
│               │  ┌────────────────────────────────────┐   │
│               │  │ 4. EXTERNAL API CALL                │   │
│               │  │ GET https://restapi.e-conomic.com  │   │
│               │  │   /invoices/booked?filter=...      │   │
│               │  └────────────┬───────────────────────┘   │
│               │               │                            │
│               │               ▼                            │
│               │  ┌────────────────────────────────────┐   │
│               │  │ Raw E-conomic Response             │   │
│               │  │ {                                  │   │
│               │  │   "collection": [                  │   │
│               │  │     {                              │   │
│               │  │       "bookedInvoiceNumber": 10001,│   │
│               │  │       "date": "2025-11-15",        │   │
│               │  │       "remainder": 25000,          │   │
│               │  │       "references": {              │   │
│               │  │         "salesPerson": {           │   │
│               │  │           "employeeNumber": 3      │   │
│               │  │         }                          │   │
│               │  │       }                            │   │
│               │  │     }                              │   │
│               │  │   ]                                │   │
│               │  │ }                                  │   │
│               │  └────────────┬───────────────────────┘   │
│               │               │                            │
│               │               ▼                            │
│               │  ┌────────────────────────────────────┐   │
│               │  │ 5. DATA TRANSFORMATION              │   │
│               │  │ - Filter: overdue only             │   │
│               │  │ - Group by employeeNumber          │   │
│               │  │ - Format each invoice              │   │
│               │  │ - Calculate daysOverdue            │   │
│               │  │ - Sort by daysOverdue DESC         │   │
│               │  └────────────┬───────────────────────┘   │
│               │               │                            │
│               └───────────────┴───────────┐                │
│                                           │                │
│    ┌──────────────────────────────────────▼─────┐         │
│    │ Return Grouped Collection                  │         │
│    │ {                                          │         │
│    │   "3": {                                   │         │
│    │     "employeeName": "Jesper Nielsen",      │         │
│    │     "invoiceCount": 3,                     │         │
│    │     "totalRemainder": 85000.00,            │         │
│    │     "invoices": [...]                      │         │
│    │   }                                        │         │
│    │ }                                          │         │
│    └──────────────────┬─────────────────────────┘         │
└───────────────────────┼───────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│ 6. CONTROLLER RETURNS VIEW                                  │
│    return view('dashboard.index', [                         │
│        'invoicesByEmployee' => $invoicesByEmployee,         │
│        'totals' => $totals,                                 │
│        'dataQuality' => $dataQuality,                       │
│        'lastUpdated' => '06-01-2026 14:30',                 │
│        'currentFilter' => 'overdue'                         │
│    ]);                                                      │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ 7. VIEW RENDERING (dashboard/index.blade.php)              │
│    ┌──────────────────────────────────────────┐            │
│    │ Filter Buttons                           │            │
│    │ - Highlight current: 'overdue'          │            │
│    └──────────────────────────────────────────┘            │
│    ┌──────────────────────────────────────────┐            │
│    │ Employee Dropdown                        │            │
│    │ @foreach($invoicesByEmployee as $emp)   │            │
│    │   <option>Jesper Nielsen (3)</option>   │            │
│    └──────────────────────────────────────────┘            │
│    ┌──────────────────────────────────────────┐            │
│    │ Data Info Banner                         │            │
│    │ Total: 6 invoices                       │            │
│    │ ⚠️ 15 unassigned (12%)                  │            │
│    └──────────────────────────────────────────┘            │
│    ┌──────────────────────────────────────────┐            │
│    │ Invoice List Partial                     │            │
│    │ @include('partials.invoice-list')       │            │
│    │   ├─ Employee: Jesper Nielsen           │            │
│    │   │  ├─ Invoice #10003 (66 days)        │            │
│    │   │  ├─ Invoice #10001 (36 days)        │            │
│    │   │  └─ Invoice #10002 (32 days)        │            │
│    │   ├─ Employee: Maria Hansen             │            │
│    │   └─ Employee: Unassigned               │            │
│    └──────────────────────────────────────────┘            │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│ 8. RENDERED HTML SENT TO BROWSER                           │
│    - Employee sections with collapsible tables             │
│    - Color-coded rows (red=critical, yellow=warning)       │
│    - Status badges showing days overdue                    │
│    - Send reminder buttons for each invoice                │
│    - JavaScript for filtering, searching, sorting          │
└─────────────────────────────────────────────────────────────┘
```

### Data Transformation Timeline

#### Step 1: Raw E-conomic API Response
```json
{
  "bookedInvoiceNumber": 10001,
  "date": "2025-11-15",
  "dueDate": "2025-12-01",
  "grossAmount": 25000.00,
  "remainder": 25000.00,
  "references": {
    "salesPerson": {
      "employeeNumber": 3,
      "name": "Jesper Nielsen"
    },
    "other": "WC-2547"
  }
}
```

#### Step 2: After `formatInvoice()` (Service)
```php
[
    "invoiceNumber" => 10001,
    "kundenr" => 1001,
    "kundenavn" => "Restaurant Nordic A/S",
    "overskrift" => "Order 2547 - Industrial ventilation system",
    "beloeb" => 25000.00,
    "remainder" => 25000.00,
    "currency" => "DKK",
    "eksterntId" => "WC-2547",
    "date" => "2025-11-15",
    "dueDate" => "2025-12-01",
    "daysOverdue" => 36,        // ← Calculated
    "daysTillDue" => 0,         // ← Calculated
    "status" => "overdue",      // ← Calculated
    "pdfUrl" => "https://..."
]
```

#### Step 3: After `getInvoicesByEmployee()` (Service)
```php
[
    "3" => [
        "employeeNumber" => 3,
        "employeeName" => "Jesper Nielsen",  // ← Fetched separately
        "invoiceCount" => 3,                 // ← Calculated
        "totalAmount" => 85000.00,          // ← Sum
        "totalRemainder" => 85000.00,       // ← Sum
        "invoices" => [
            // Formatted invoices sorted by daysOverdue DESC
        ]
    ]
]
```

#### Step 4: In View - Final HTML
```html
<tr class="bg-red-50">  <!-- Background color based on daysOverdue -->
    <td>📄 10001</td>
    <td>10.10.25</td>  <!-- Formatted date -->
    <td>1001</td>
    <td>Restaurant Nordic A/S</td>
    <td>Order 2547 - Industrial ventilatio...</td>  <!-- Truncated -->
    <td>25.000,00</td>  <!-- Danish number format -->
    <td class="text-red-600">25.000,00</td>  <!-- Red text -->
    <td>DKK</td>
    <td>
        <span class="bg-red-100 text-red-800">
            36 days overdue
        </span>
    </td>
    <td>WC-2547</td>
    <td>
        <button onclick="sendReminder(10001, 1001, this)">
            📧 Email
        </button>
    </td>
</tr>
```

### Key Calculations in Service Layer

#### 1. Days Overdue Calculation
**Location**: `app/Services/EconomicInvoiceService.php:363-370`

```php
$dueDate = Carbon::parse($invoice['dueDate']);  // "2025-12-01"
$now = now();                                    // "2026-01-06"

$isOverdue = !$isPaid && $dueDate->isPast();    // true
$daysOverdue = $isOverdue
    ? (int) $dueDate->diffInDays($now)          // 36 days
    : 0;
```

#### 2. Status Determination
**Location**: `app/Services/EconomicInvoiceService.php:373`

```php
$isPaid = $invoice['remainder'] == 0;           // false (25000 > 0)
$isOverdue = !$isPaid && $dueDate->isPast();    // true

$status = $isPaid ? 'paid' : ($isOverdue ? 'overdue' : 'unpaid');
// Result: 'overdue'
```

#### 3. Employee Grouping
**Location**: `app/Services/EconomicInvoiceService.php:305-316`

```php
return $invoices->groupBy(function ($invoice) {
    return $invoice['references']['salesPerson']['employeeNumber'] ?? 'unassigned';
})->map(function ($group, $employeeNumber) {
    return [
        'employeeNumber' => $employeeNumber,                    // 3
        'employeeName' => $this->getEmployeeName($employeeNumber), // "Jesper Nielsen"
        'invoiceCount' => $group->count(),                      // 3
        'totalAmount' => $group->sum('grossAmount'),           // 85000.00
        'totalRemainder' => $group->sum('remainder'),          // 85000.00
        'invoices' => $group->map(fn($inv) => $this->formatInvoice($inv))
                            ->sortByDesc('daysOverdue'),
    ];
});
```

---

## Interactive Features

### HTMX Refresh (Real-time Update)

**Location**: `dashboard/index.blade.php:40-45`

```html
<button
    hx-get="{{ route('dashboard.refresh', ['filter' => 'overdue']) }}"
    hx-target="#invoice-list"
    hx-swap="innerHTML"
    hx-indicator="#loading">
    Refresh Data
</button>
```

**What Happens**:
1. User clicks "Refresh Data"
2. HTMX sends: `GET /dashboard/refresh?filter=overdue`
3. Controller clears cache and fetches fresh data
4. Returns `dashboard/partials/invoice-list.blade.php` HTML
5. HTMX swaps content of `#invoice-list` div
6. **No page reload** - seamless update

### Send Reminder (AJAX)

**Location**: `dashboard/index.blade.php:195-247`

```javascript
async function sendReminder(invoiceNumber, customerNumber, button) {
    button.innerHTML = '⏳ Sending...';

    const response = await fetch('/reminders/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            invoice_number: invoiceNumber,     // 10001
            customer_number: customerNumber    // 1001
        })
    });

    const data = await response.json();

    if (data.success) {
        button.innerHTML = '✅ Sent';
        button.classList.add('bg-green-600');
    }
}
```

**Data Flow**:
1. User clicks "📧 Email" button
2. JavaScript sends POST to `/reminders/send`
3. Controller validates, fetches customer email from E-conomic
4. Sends email via `InvoiceReminderMail`
5. Logs reminder in `invoice_reminders` table
6. Returns JSON: `{"success": true, "message": "Reminder sent"}`
7. Button changes to green checkmark

### Client-Side Filtering

**Location**: `dashboard/index.blade.php:171-180`

```javascript
function filterByEmployee(employeeNumber) {
    const sections = document.querySelectorAll('[data-employee-section]');
    sections.forEach(section => {
        if (employeeNumber === '' || section.dataset.employeeSection === employeeNumber) {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });
}
```

**Example**:
- User selects "Jesper Nielsen (3)" from dropdown
- `filterByEmployee('3')` is called
- All sections with `data-employee-section="3"` shown
- All other sections hidden
- **No server request** - instant filtering

---

## Summary Table

| View | Data Source | Key Variables | Visual Elements |
|------|-------------|---------------|-----------------|
| **Dashboard** | `DashboardController::index()` | `$invoicesByEmployee`, `$currentFilter`, `$dataQuality` | Filter buttons, employee dropdown, invoice table, reminder buttons |
| **Invoice List Partial** | Included from Dashboard | `$employeeData`, `$invoice` | Collapsible employee sections, color-coded rows, status badges |
| **Statistics** | `DashboardController::stats()` | `$invoicesByEmployee`, `$totals` | Stat cards, critical alerts, Chart.js graphs |
| **Reminders** | `ReminderController::index()` | `$reminders`, `$stats` | Stats cards, reminder history table, pagination |
| **Users** | `UserController::index()` | `$users` | User list table, admin badges, delete buttons |

---

## Best Practices Observed

1. **Data Transformation in Service Layer**: Raw API data transformed before reaching views
2. **Eager Loading**: `$reminders->with('sentBy:id,name')` prevents N+1 queries
3. **Caching**: 30-minute cache reduces API calls
4. **Pagination**: Reminders paginated at 50 per page
5. **Real-time Updates**: HTMX for no-reload refreshes
6. **Client-side Filtering**: JavaScript for instant filtering without server roundtrips
7. **Color Coding**: Visual severity indicators (red > 30 days, yellow > 14 days)
8. **Defensive Coding**: `isset()` checks, `??` operators, `@empty` directives
9. **Number Formatting**: Danish format (`,` decimal, `.` thousands)
10. **Security**: CSRF tokens, admin middleware, confirmation dialogs

---

*Last Updated: January 6, 2026*
