# BV Economic Dashboard - API Documentation

## Table of Contents
1. [Overview](#overview)
2. [E-conomic API Integration](#e-conomic-api-integration)
3. [Internal API Endpoints](#internal-api-endpoints)
4. [Data Structures](#data-structures)
5. [Data Grouping Logic](#data-grouping-logic)
6. [Sample Responses](#sample-responses)
7. [Caching Strategy](#caching-strategy)
8. [Demo Mode](#demo-mode)

---

## Overview

**BilligVentilation Economic Dashboard** is a Laravel-based application that integrates with the **e-conomic REST API** to manage and track invoices, reminders, and employee performance.

### Key Features
- Real-time invoice tracking (overdue, unpaid, paid)
- Invoice grouping by salesperson/employee
- Automated reminder system for customers and employees
- Caching mechanism for performance optimization
- Multi-language support (English, Danish)

### Technology Stack
- **Backend**: Laravel 11.x
- **External API**: e-conomic REST API
- **Database**: MySQL/SQLite
- **Caching**: Laravel Cache (File/Redis)
- **HTTP Client**: Laravel HTTP Facade

---

## E-conomic API Integration

### Base Configuration

**Base URL**: `https://restapi.e-conomic.com`

**Authentication Headers**:
```php
[
    'X-AppSecretToken' => config('e-conomic.app_secret_token'),
    'X-AgreementGrantToken' => config('e-conomic.agreement_grant_token'),
    'Content-Type' => 'application/json',
]
```

### External Endpoints Used

#### 1. Get Booked Invoices
```http
GET https://restapi.e-conomic.com/invoices/booked?pagesize=1000&filter=date$gte:2025-07-06
```

**Purpose**: Fetch all booked invoices from the last N months (configurable, default 6 months)

**Query Parameters**:
- `pagesize`: Number of records per page (max 1000)
- `filter`: Date filter to limit results (`date$gte:YYYY-MM-DD`)

**Response Structure**:
```json
{
  "collection": [
    {
      "bookedInvoiceNumber": 10001,
      "date": "2025-11-15",
      "dueDate": "2025-12-01",
      "currency": "DKK",
      "grossAmount": 25000.00,
      "remainder": 25000.00,
      "customer": {
        "customerNumber": 1001
      },
      "recipient": {
        "name": "Restaurant Nordic A/S"
      },
      "notes": {
        "heading": "Order 2547 - Industrial ventilation system"
      },
      "references": {
        "salesPerson": {
          "employeeNumber": 3,
          "name": "Jesper Nielsen"
        },
        "other": "WC-2547"
      },
      "pdf": {
        "download": "https://restapi.e-conomic.com/invoices/booked/10001/pdf"
      }
    }
  ],
  "pagination": {
    "pageNumber": 0,
    "pageSize": 1000,
    "maxPageSizeAllowed": 1000,
    "results": 127
  }
}
```

#### 2. Get Customer Details
```http
GET https://restapi.e-conomic.com/customers/{customerNumber}
```

**Purpose**: Fetch customer email for sending reminders

**Response Structure**:
```json
{
  "customerNumber": 1001,
  "email": "contact@restaurant-nordic.dk",
  "name": "Restaurant Nordic A/S",
  "address": "Vesterbrogade 123",
  "city": "Copenhagen",
  "zip": "1620"
}
```

#### 3. Get Employee Details
```http
GET https://restapi.e-conomic.com/employees/{employeeNumber}
```

**Purpose**: Fetch employee name and email

**Response Structure**:
```json
{
  "employeeNumber": 3,
  "name": "Jesper Nielsen",
  "email": "jesper@billigventilation.dk",
  "employeeGroup": {
    "employeeGroupNumber": 1
  }
}
```

#### 4. Get Invoice Totals
```http
GET https://restapi.e-conomic.com/invoices/totals
```

**Purpose**: Fetch overall invoice statistics

**Response Structure**:
```json
{
  "drafts": 12,
  "booked": 1547,
  "overdue": 23,
  "notDue": 45,
  "paid": 1479
}
```

---

## Internal API Endpoints

### 1. Get Overdue Invoices (API)

**Endpoint**: `GET /api/overdue`

**Authentication**: None (consider adding auth in production)

**Description**: Returns overdue invoices grouped by employee/salesperson

**Response**:
```json
{
  "data": {
    "3": {
      "employeeNumber": 3,
      "employeeName": "Jesper Nielsen",
      "invoiceCount": 3,
      "totalAmount": 85000.00,
      "totalRemainder": 85000.00,
      "invoices": [
        {
          "invoiceNumber": 10003,
          "kundenr": 1003,
          "kundenavn": "Office Solutions Denmark",
          "overskrift": "HVAC system installation - Building 3",
          "beloeb": 45000.00,
          "remainder": 45000.00,
          "currency": "DKK",
          "eksterntId": null,
          "date": "2025-10-10",
          "dueDate": "2025-11-01",
          "daysOverdue": 66,
          "daysTillDue": 0,
          "status": "overdue",
          "pdfUrl": "https://restapi.e-conomic.com/invoices/booked/10003/pdf"
        }
      ]
    }
  },
  "meta": {
    "fetched_at": "2026-01-06T14:30:00+00:00"
  }
}
```

### 2. Get Dashboard Statistics

**Endpoint**: `GET /dashboard/stats`

**Authentication**: Required (`auth` middleware)

**Description**: Returns stats page view with charts and analytics

**Query Parameters**:
- `filter` (optional): `all`, `overdue`, or `unpaid` (default: `overdue`)

**Response**: HTML view with invoice statistics

### 3. Refresh Invoice Data

**Endpoint**: `GET /dashboard/refresh`

**Authentication**: Required (`auth` middleware)

**Description**: Clears cache and refreshes invoice list (HTMX partial)

**Query Parameters**:
- `filter` (optional): `all`, `overdue`, or `unpaid`

**Response**: HTML partial (`dashboard.partials.invoice-list`)

### 4. Send Customer Reminder

**Endpoint**: `POST /reminders/send`

**Authentication**: Required (`auth` middleware)

**Request Body**:
```json
{
  "invoice_number": 10001,
  "customer_number": 1001
}
```

**Validation Rules**:
- `invoice_number`: required, integer
- `customer_number`: required, integer

**Success Response** (200):
```json
{
  "success": true,
  "message": "Reminder sent successfully"
}
```

**Error Responses**:

*Recent reminder already sent* (422):
```json
{
  "success": false,
  "message": "A reminder was already sent 3 days ago. Please wait before sending another."
}
```

*Customer email not found* (404):
```json
{
  "success": false,
  "message": "Customer email not found in e-conomic"
}
```

*Send failed* (500):
```json
{
  "success": false,
  "message": "Failed to send reminder: Connection timeout"
}
```

**Rate Limiting**: One reminder per invoice every 7 days

### 5. Send Employee Reminder

**Endpoint**: `POST /reminders/send-employee`

**Authentication**: Required (`auth` middleware)

**Request Body**:
```json
{
  "employee_number": 3
}
```

**Success Response** (200):
```json
{
  "success": true,
  "message": "Employee reminder sent successfully"
}
```

**Error Response** (404):
```json
{
  "success": false,
  "message": "No overdue invoices found for this employee"
}
```

### 6. Get Reminder History

**Endpoint**: `GET /reminders/{invoiceNumber}/history`

**Authentication**: Required (`auth` middleware)

**Response**:
```json
{
  "success": true,
  "reminders": [
    {
      "id": 15,
      "invoice_number": 10001,
      "customer_email": "contact@restaurant-nordic.dk",
      "customer_name": "Restaurant Nordic A/S",
      "amount_due": "25000.00",
      "email_sent": true,
      "email_error": null,
      "created_at": "2026-01-05T10:30:00.000000Z",
      "sent_by": {
        "id": 1,
        "name": "Admin User"
      }
    }
  ]
}
```

---

## Data Structures

### Invoice Object (Raw from E-conomic)

```json
{
  "bookedInvoiceNumber": 10001,
  "date": "2025-11-15",
  "dueDate": "2025-12-01",
  "currency": "DKK",
  "grossAmount": 25000.00,
  "remainder": 25000.00,
  "customer": {
    "customerNumber": 1001
  },
  "recipient": {
    "name": "Restaurant Nordic A/S"
  },
  "notes": {
    "heading": "Order 2547 - Industrial ventilation system"
  },
  "references": {
    "salesPerson": {
      "employeeNumber": 3,
      "name": "Jesper Nielsen"
    },
    "other": "WC-2547"
  },
  "pdf": {
    "download": "https://example.com/invoice.pdf"
  }
}
```

### Formatted Invoice Object (Internal)

```json
{
  "invoiceNumber": 10001,
  "kundenr": 1001,
  "kundenavn": "Restaurant Nordic A/S",
  "overskrift": "Order 2547 - Industrial ventilation system",
  "beloeb": 25000.00,
  "remainder": 25000.00,
  "currency": "DKK",
  "eksterntId": "WC-2547",
  "date": "2025-11-15",
  "dueDate": "2025-12-01",
  "daysOverdue": 36,
  "daysTillDue": 0,
  "status": "overdue",
  "pdfUrl": "https://example.com/invoice.pdf"
}
```

### Employee Group Object

```json
{
  "employeeNumber": 3,
  "employeeName": "Jesper Nielsen",
  "invoiceCount": 5,
  "totalAmount": 105500.00,
  "totalRemainder": 105500.00,
  "invoices": [
    {
      "invoiceNumber": 10001,
      "kundenr": 1001,
      "kundenavn": "Restaurant Nordic A/S",
      "overskrift": "Order 2547 - Industrial ventilation system",
      "beloeb": 25000.00,
      "remainder": 25000.00,
      "currency": "DKK",
      "eksterntId": "WC-2547",
      "date": "2025-11-15",
      "dueDate": "2025-12-01",
      "daysOverdue": 36,
      "daysTillDue": 0,
      "status": "overdue",
      "pdfUrl": "https://example.com/invoice.pdf"
    }
  ]
}
```

---

## Data Grouping Logic

### Invoice Filtering

The system supports three filter modes:

1. **`overdue`** (default)
   - `remainder > 0` AND `dueDate < today`
   - Sorted by `daysOverdue` (descending)

2. **`unpaid`**
   - `remainder > 0`
   - Includes both overdue and not-yet-due invoices

3. **`all`**
   - All invoices (paid and unpaid)
   - From last 6 months (configurable)

### Grouping by Employee

**Service Method**: `EconomicInvoiceService::getInvoicesByEmployee($filter)`

**Process**:
1. Fetch invoices based on filter (`all`, `overdue`, or `unpaid`)
2. Group by `references.salesPerson.employeeNumber`
3. Invoices without a salesperson are grouped under `'unassigned'`
4. Calculate totals for each group:
   - `invoiceCount`: Number of invoices
   - `totalAmount`: Sum of `grossAmount`
   - `totalRemainder`: Sum of `remainder`
5. Format each invoice using `formatInvoice()` method
6. Sort invoices by `daysOverdue` (descending)

**Code Reference**: `app/Services/EconomicInvoiceService.php:280-317`

### Status Calculation

```php
$isPaid = $invoice['remainder'] == 0;
$isOverdue = !$isPaid && $dueDate->isPast();

$status = $isPaid ? 'paid' : ($isOverdue ? 'overdue' : 'unpaid');
```

**Statuses**:
- `paid`: `remainder = 0`
- `overdue`: `remainder > 0` AND `dueDate < today`
- `unpaid`: `remainder > 0` AND `dueDate >= today`

### Days Calculation

```php
// Days overdue (positive number, only if overdue)
$daysOverdue = $isOverdue ? (int) $dueDate->diffInDays($now) : 0;

// Days till due (positive number, only if not yet due)
$daysTillDue = !$isPaid && !$isOverdue ? (int) $now->diffInDays($dueDate) : 0;
```

---

## Sample Responses

### Complete API Response Example

**Request**: `GET /api/overdue`

**Response**:
```json
{
  "data": {
    "3": {
      "employeeNumber": 3,
      "employeeName": "Jesper Nielsen",
      "invoiceCount": 3,
      "totalAmount": 85000.00,
      "totalRemainder": 85000.00,
      "invoices": [
        {
          "invoiceNumber": 10003,
          "kundenr": 1003,
          "kundenavn": "Office Solutions Denmark",
          "overskrift": "HVAC system installation - Building 3",
          "beloeb": 45000.00,
          "remainder": 45000.00,
          "currency": "DKK",
          "eksterntId": null,
          "date": "2025-10-10",
          "dueDate": "2025-11-01",
          "daysOverdue": 66,
          "daysTillDue": 0,
          "status": "overdue",
          "pdfUrl": "https://example.com/invoice.pdf"
        },
        {
          "invoiceNumber": 10001,
          "kundenr": 1001,
          "kundenavn": "Restaurant Nordic A/S",
          "overskrift": "Order 2547 - Industrial ventilation system",
          "beloeb": 25000.00,
          "remainder": 25000.00,
          "currency": "DKK",
          "eksterntId": "WC-2547",
          "date": "2025-11-15",
          "dueDate": "2025-12-01",
          "daysOverdue": 36,
          "daysTillDue": 0,
          "status": "overdue",
          "pdfUrl": "https://example.com/invoice.pdf"
        },
        {
          "invoiceNumber": 10002,
          "kundenr": 1002,
          "kundenavn": "Copenhagen Hotels ApS",
          "overskrift": "Order 2589 - Kitchen exhaust fan replacement",
          "beloeb": 15000.00,
          "remainder": 15000.00,
          "currency": "DKK",
          "eksterntId": "WC-2589",
          "date": "2025-11-20",
          "dueDate": "2025-12-05",
          "daysOverdue": 32,
          "daysTillDue": 0,
          "status": "overdue",
          "pdfUrl": "https://example.com/invoice.pdf"
        }
      ]
    },
    "7": {
      "employeeNumber": 7,
      "employeeName": "Maria Hansen",
      "invoiceCount": 2,
      "totalAmount": 20500.00,
      "totalRemainder": 20500.00,
      "invoices": [
        {
          "invoiceNumber": 10004,
          "kundenr": 1004,
          "kundenavn": "Retail Chain Denmark A/S",
          "overskrift": "Order 2601 - Air conditioning maintenance",
          "beloeb": 12000.00,
          "remainder": 12000.00,
          "currency": "DKK",
          "eksterntId": "WC-2601",
          "date": "2025-11-25",
          "dueDate": "2025-12-10",
          "daysOverdue": 27,
          "daysTillDue": 0,
          "status": "overdue",
          "pdfUrl": "https://example.com/invoice.pdf"
        },
        {
          "invoiceNumber": 10005,
          "kundenr": 1005,
          "kundenavn": "Manufacturing Co. ApS",
          "overskrift": "Order 2534 - Ventilation ducts and filters",
          "beloeb": 8500.00,
          "remainder": 8500.00,
          "currency": "DKK",
          "eksterntId": "WC-2534",
          "date": "2025-11-01",
          "dueDate": "2025-11-20",
          "daysOverdue": 47,
          "daysTillDue": 0,
          "status": "overdue",
          "pdfUrl": "https://example.com/invoice.pdf"
        }
      ]
    },
    "unassigned": {
      "employeeNumber": "unassigned",
      "employeeName": "Unassigned",
      "invoiceCount": 1,
      "totalAmount": 9800.00,
      "totalRemainder": 9800.00,
      "invoices": [
        {
          "invoiceNumber": 10010,
          "kundenr": 1010,
          "kundenavn": "Sample Company A/S",
          "overskrift": "Order 2650 - Maintenance service",
          "beloeb": 9800.00,
          "remainder": 9800.00,
          "currency": "DKK",
          "eksterntId": "WC-2650",
          "date": "2025-11-18",
          "dueDate": "2025-12-03",
          "daysOverdue": 34,
          "daysTillDue": 0,
          "status": "overdue",
          "pdfUrl": "https://example.com/invoice.pdf"
        }
      ]
    }
  },
  "meta": {
    "fetched_at": "2026-01-06T14:30:00+00:00"
  }
}
```

### Data Quality Statistics

**Response Structure**:
```json
{
  "has_unassigned": true,
  "unassigned_count": 15,
  "total_count": 127,
  "percentage": 12,
  "message": "15 out of 127 invoices (12%) have no salesperson assigned.",
  "suggestion": "Assign salespeople to invoices in your e-conomic dashboard for better tracking."
}
```

**Code Reference**: `app/Services/EconomicInvoiceService.php:410-435`

---

## Caching Strategy

### Cache Keys

| Cache Key | Duration | Purpose |
|-----------|----------|---------|
| `overdue_invoices` | 30 minutes* | Overdue invoices (remainder > 0, past due) |
| `unpaid_invoices` | 30 minutes* | Unpaid invoices (remainder > 0) |
| `all_invoices` | 30 minutes* | All invoices from last 6 months |
| `invoice_totals` | 5 minutes | Overall statistics from e-conomic |
| `employee_{number}` | 60 minutes | Employee name by number |

*Configurable via `config('e-conomic.cache_duration')` (in minutes)

### Cache Clearing

**Manual Refresh**: `GET /dashboard/refresh`
- Calls `EconomicInvoiceService::clearCache()`
- Clears: `overdue_invoices`, `unpaid_invoices`, `all_invoices`, `invoice_totals`

**Code Reference**: `app/Services/EconomicInvoiceService.php:440-446`

### Performance Optimization

**Date Filter**: Only fetch invoices from the last N months (default: 6)

```php
$months = config('e-conomic.sync_months', 6);
$dateFrom = now()->subMonths($months)->format('Y-m-d');
$url = "{$baseUrl}/invoices/booked?pagesize=1000&filter=date\$gte:{$dateFrom}";
```

**Reason**: Prevents loading 21,000+ invoices which can freeze the browser.

**Configuration**: `config/e-conomic.php`
```php
return [
    'sync_months' => env('ECONOMIC_SYNC_MONTHS', 6),
    'cache_duration' => env('ECONOMIC_CACHE_DURATION', 30), // minutes
];
```

---

## Demo Mode

### Activation

Demo mode is activated when:
```php
config('e-conomic.app_secret_token') === 'demo'
```

**Environment Variable**:
```env
ECONOMIC_APP_SECRET_TOKEN=demo
```

### Mock Data

When in demo mode, the system returns hardcoded mock invoices instead of calling the e-conomic API.

**Mock Invoices**: 9 sample invoices
- 5 overdue invoices
- 2 unpaid (not yet due) invoices
- 2 paid invoices

**Mock Employees**:
- Employee #3: Jesper Nielsen
- Employee #7: Maria Hansen

**Mock Emails**:
- Customer: `customer@example.com`
- Employee: `employee@billigventilation.dk`

**Code Reference**: `app/Services/EconomicInvoiceService.php:35-186`

### Sample Mock Invoice

```json
{
  "bookedInvoiceNumber": 10001,
  "date": "2025-11-15",
  "dueDate": "2025-12-01",
  "currency": "DKK",
  "grossAmount": 25000.00,
  "remainder": 25000.00,
  "customer": {
    "customerNumber": 1001
  },
  "recipient": {
    "name": "Restaurant Nordic A/S"
  },
  "notes": {
    "heading": "Order 2547 - Industrial ventilation system"
  },
  "references": {
    "salesPerson": {
      "employeeNumber": 3,
      "name": "Jesper Nielsen"
    },
    "other": "WC-2547"
  },
  "pdf": {
    "download": "https://example.com/invoice.pdf"
  }
}
```

---

## Configuration Reference

### Environment Variables

```env
# E-conomic API Credentials
ECONOMIC_APP_SECRET_TOKEN=your_app_secret_token
ECONOMIC_AGREEMENT_GRANT_TOKEN=your_agreement_grant_token

# Performance Settings
ECONOMIC_SYNC_MONTHS=6          # Number of months to sync (default: 6)
ECONOMIC_CACHE_DURATION=30      # Cache duration in minutes (default: 30)

# Demo Mode (for testing without API)
ECONOMIC_APP_SECRET_TOKEN=demo  # Enables mock data
```

### Config File: `config/e-conomic.php`

```php
<?php

return [
    'app_secret_token' => env('ECONOMIC_APP_SECRET_TOKEN'),
    'agreement_grant_token' => env('ECONOMIC_AGREEMENT_GRANT_TOKEN'),
    'sync_months' => env('ECONOMIC_SYNC_MONTHS', 6),
    'cache_duration' => env('ECONOMIC_CACHE_DURATION', 30),
];
```

---

## Error Handling

### Common Errors

#### 1. Authentication Failed
```json
{
  "message": "Invalid authentication credentials",
  "errorCode": "E01100"
}
```

**Cause**: Invalid `X-AppSecretToken` or `X-AgreementGrantToken`

**Solution**: Verify credentials in `.env` file

#### 2. Customer Email Not Found
```json
{
  "success": false,
  "message": "Customer email not found in e-conomic"
}
```

**Cause**: Customer record in e-conomic has no email address

**Solution**: Update customer record in e-conomic dashboard

#### 3. Rate Limit - Reminder Already Sent
```json
{
  "success": false,
  "message": "A reminder was already sent 3 days ago. Please wait before sending another."
}
```

**Cause**: Reminder sent within the last 7 days

**Solution**: Wait until 7-day cooldown period expires

#### 4. Cache Timeout
**Symptom**: Slow page loads, timeouts

**Solution**:
- Reduce `ECONOMIC_SYNC_MONTHS` (e.g., from 6 to 3 months)
- Increase `ECONOMIC_CACHE_DURATION` to reduce API calls

---

## Development Notes

### Data Quality Logging

The system logs warnings when invoices lack salesperson assignments:

```php
\Log::warning("E-conomic Data Quality: {$unassignedCount} out of {$totalInvoices} invoices ({$percentage}%) have no salesperson assigned.", [
    'filter' => $filter,
    'total_invoices' => $totalInvoices,
    'unassigned_count' => $unassignedCount,
    'suggestion' => 'Assign salespeople to invoices in e-conomic dashboard for better tracking'
]);
```

**Check Logs**: `storage/logs/laravel.log`

### Testing Endpoints

Use tools like **Postman** or **curl** to test API endpoints:

```bash
# Get overdue invoices
curl -X GET http://localhost:8000/api/overdue

# Send customer reminder (requires authentication)
curl -X POST http://localhost:8000/reminders/send \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your_csrf_token" \
  -d '{"invoice_number": 10001, "customer_number": 1001}'

# Get reminder history
curl -X GET http://localhost:8000/reminders/10001/history
```

---

## Changelog

| Date | Version | Changes |
|------|---------|---------|
| 2026-01-06 | 1.0 | Initial API documentation |

---

## Support

For issues or questions, please contact the development team or check:
- Laravel Logs: `storage/logs/laravel.log`
- E-conomic API Docs: https://restdocs.e-conomic.com/
- Project Repository: Contact admin for access
