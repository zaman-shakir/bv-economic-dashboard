# Performance Optimization Plan
## BV Economic Dashboard Speed Improvements

**Generated:** January 8, 2026
**Current Status:** Database-driven with basic caching

---

## Executive Summary

The dashboard is already well-optimized with:
- ✅ Database storage instead of API calls
- ✅ Good database indexes
- ✅ Eager loading to prevent N+1 queries
- ✅ Query result limiting (100 invoices per employee)
- ✅ Basic caching on API methods

**Potential Speed Improvements: 30-60% faster page loads**

---

## Current Performance Analysis

### **What's Working Well:**

1. **Database Architecture** ✅
   - Invoices stored locally (not fetching from API on every request)
   - Good indexes on key columns (invoice_number, employee_number, due_date, remainder)
   - Composite index on [due_date, remainder] for overdue queries

2. **Query Optimization** ✅
   - Eager loading comments with `withCount('comments')`
   - Eager loading relationships with `with(['comments'])`
   - Limited to 100 invoices per employee
   - Grouped queries to avoid N+1 problems

3. **Caching (API Methods)** ✅
   - Cache duration: 300-3600 seconds
   - Caching employee data, totals, and invoice lists

### **Performance Bottlenecks:**

1. **❌ No caching on database queries** (PRIMARY ISSUE)
   - Every page load queries database
   - `getInvoicesByEmployeeFromDatabase()` runs complex queries on each request
   - No cache for filtered/grouped results

2. **❌ Duplicate filter logic**
   - Lines 737-747 and 795-802 repeat same filter logic
   - Could be extracted to reusable query scopes

3. **❌ Large view rendering**
   - 1,324 lines in `dashboard/index.blade.php`
   - 300+ lines in `invoice-list.blade.php`
   - Heavy JavaScript (1000+ lines)

4. **❌ Comment count queries**
   - `withCount('comments')` runs for every invoice
   - Could be cached or aggregated

5. **❌ Session writes on every request**
   - Lines 27-32 in DashboardController write to session
   - Even if values haven't changed

---

## Optimization Recommendations

### **Priority 1: HIGH IMPACT (Quick Wins)**

#### **1.1 Add Response Caching (Estimated: 40-60% faster)**

Cache the entire dashboard response for each filter/grouping combination.

**Implementation:**
```php
// In DashboardController@index
public function index(Request $request): View
{
    $filter = $request->get('filter', session('dashboard.filter', 'overdue'));
    $grouping = $request->get('grouping', session('dashboard.grouping', 'employee'));
    $dateFrom = $request->get('date_from');
    $dateTo = $request->get('date_to');
    $search = $request->get('search');

    // Create cache key based on parameters
    $cacheKey = "dashboard_{$filter}_{$grouping}_" . md5($dateFrom . $dateTo . $search);

    // Cache for 5 minutes (300 seconds)
    $data = Cache::remember($cacheKey, 300, function() use ($filter, $grouping, $dateFrom, $dateTo, $search) {
        // ... existing query logic ...
        return [
            'invoicesByEmployee' => $invoicesByEmployee,
            'totals' => $totals,
            'dataQuality' => $dataQuality,
            // ... other data
        ];
    });

    return view('dashboard.index', $data);
}
```

**Benefits:**
- First load: Same speed
- Subsequent loads within 5 minutes: 10x faster
- No database queries for cached responses
- Automatic cache invalidation after 5 minutes

---

#### **1.2 Add Database Query Caching (Estimated: 20-30% faster)**

Cache individual query results in the service layer.

**Implementation:**
```php
// In EconomicInvoiceService
public function getInvoicesByEmployeeFromDatabase(...): Collection
{
    $cacheKey = "invoices_employee_{$filter}_" . md5($dateFrom . $dateTo . $search . $hasComments . $commentDateFilter);

    return Cache::remember($cacheKey, 300, function() use (...) {
        // ... existing query logic ...
    });
}
```

**Benefits:**
- Database queries run once every 5 minutes
- Reduces database load
- Faster response times

---

#### **1.3 Optimize Session Writes (Estimated: 5-10% faster)**

Only write to session if value has actually changed.

**Implementation:**
```php
// In DashboardController@index
if ($request->has('filter') && session('dashboard.filter') !== $filter) {
    session(['dashboard.filter' => $filter]);
}
if ($request->has('grouping') && session('dashboard.grouping') !== $grouping) {
    session(['dashboard.grouping' => $grouping]);
}
```

**Benefits:**
- Fewer session writes
- Less I/O operations
- Faster response times

---

### **Priority 2: MEDIUM IMPACT**

#### **2.1 Add Composite Indexes (Estimated: 10-15% faster)**

Add more composite indexes for common query patterns.

**Create Migration:**
```php
Schema::table('invoices', function (Blueprint $table) {
    // For employee + status queries
    $table->index(['employee_number', 'remainder'], 'idx_employee_remainder');
    $table->index(['employee_number', 'due_date'], 'idx_employee_due_date');

    // For date range queries
    $table->index(['invoice_date', 'due_date'], 'idx_date_range');

    // For search queries
    $table->index('customer_name', 'idx_customer_name');
    $table->index('external_reference', 'idx_external_ref');
});
```

**Benefits:**
- Faster WHERE clauses with multiple conditions
- Better query performance for common filters

---

#### **2.2 Implement Database Views (Estimated: 15-20% faster)**

Create database views for common aggregations.

**Create Migration:**
```sql
CREATE VIEW invoice_stats_by_employee AS
SELECT
    COALESCE(employee_number, 'unassigned') as employee_number,
    MAX(employee_name) as employee_name,
    COUNT(*) as invoice_count,
    SUM(gross_amount) as total_amount,
    SUM(remainder) as total_remainder,
    COUNT(CASE WHEN remainder > 0 AND due_date < CURDATE() THEN 1 END) as overdue_count
FROM invoices
GROUP BY COALESCE(employee_number, 'unassigned');
```

**Benefits:**
- Pre-aggregated data
- Faster dashboard loading
- Reduced query complexity

---

#### **2.3 Lazy Load JavaScript (Estimated: 20-30% faster initial load)**

Split JavaScript into critical and non-critical bundles.

**Implementation:**
- Move sort/filter functions to separate file
- Load only on user interaction
- Use `defer` or `async` on script tags

**Benefits:**
- Faster Time to Interactive (TTI)
- Better perceived performance
- Improved Lighthouse score

---

### **Priority 3: LOW IMPACT (Long-term)**

#### **3.1 Implement Redis Caching**

Switch from file-based cache to Redis.

**Configuration:**
```bash
# In .env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**Benefits:**
- Faster cache reads/writes
- Better cache invalidation
- Supports cache tagging

---

#### **3.2 Add Pagination**

Instead of limiting to 100 invoices, add proper pagination.

**Implementation:**
```php
$invoices = $invoiceQuery
    ->withCount('comments')
    ->orderBy('due_date', 'asc')
    ->paginate(50); // Show 50 per page
```

**Benefits:**
- Faster initial page load
- Better UX for large datasets
- Reduced memory usage

---

#### **3.3 Implement Partial View Rendering**

Use HTMX or similar to load invoice groups on-demand.

**Benefits:**
- Faster initial page load
- Progressive enhancement
- Better perceived performance

---

## Implementation Priority

### **Week 1: Quick Wins (Estimated 50-70% improvement)**
1. ✅ Add response caching (DashboardController)
2. ✅ Add query result caching (EconomicInvoiceService)
3. ✅ Optimize session writes

### **Week 2: Database Optimization (Estimated 15-25% additional improvement)**
4. ✅ Add composite indexes
5. ✅ Create database views for aggregations

### **Week 3: Frontend Optimization (Estimated 10-20% additional improvement)**
6. ✅ Lazy load JavaScript
7. ✅ Optimize view rendering

### **Week 4: Infrastructure (Optional)**
8. ⏳ Redis caching (if available on cPanel)
9. ⏳ Implement pagination
10. ⏳ Add partial view rendering

---

## Expected Results

### **Before Optimization:**
- Average page load: 1.5-3 seconds
- Database queries per request: 15-30
- Memory usage: 20-40 MB

### **After Quick Wins (Week 1):**
- Average page load: **0.5-1 second** (50-70% faster)
- Database queries per request: **1-3** (80-95% reduction)
- Memory usage: 15-25 MB (25% reduction)

### **After Full Optimization (Week 4):**
- Average page load: **0.3-0.6 seconds** (70-80% faster than baseline)
- Database queries per request: **0-2** (90-100% reduction with caching)
- Memory usage: 10-20 MB (50% reduction)

---

## Monitoring & Measurement

### **Tools to Use:**

1. **Laravel Telescope** (for query monitoring)
   ```bash
   composer require laravel/telescope --dev
   php artisan telescope:install
   php artisan migrate
   ```

2. **Laravel Debugbar** (for performance profiling)
   ```bash
   composer require barryvdh/laravel-debugbar --dev
   ```

3. **Browser DevTools**
   - Network tab (load times)
   - Performance tab (render times)
   - Lighthouse (overall score)

### **Metrics to Track:**

- **TTFB** (Time to First Byte): Target < 200ms
- **FCP** (First Contentful Paint): Target < 1s
- **LCP** (Largest Contentful Paint): Target < 2.5s
- **TTI** (Time to Interactive): Target < 3s
- **Database queries per request**: Target < 5
- **Memory usage**: Target < 20MB

---

## Cache Invalidation Strategy

### **When to Clear Cache:**

1. **After sync**: Clear all dashboard caches
   ```php
   // In syncAllInvoices() method
   Cache::tags(['dashboard'])->flush();
   ```

2. **After manual refresh**: Clear specific cache key
   ```php
   // When user clicks "Refresh" button
   Cache::forget($cacheKey);
   ```

3. **Time-based**: Auto-expire after 5 minutes
   ```php
   Cache::remember($key, 300, function() { ... });
   ```

### **Cache Tags Strategy:**
```php
// Tag all dashboard caches
Cache::tags(['dashboard', 'invoices'])->remember($key, 300, function() { ... });

// Clear all dashboard caches at once
Cache::tags(['dashboard'])->flush();
```

---

## Risk Assessment

### **Low Risk:**
- ✅ Response caching
- ✅ Query result caching
- ✅ Session write optimization
- ✅ Composite indexes

### **Medium Risk:**
- ⚠️ Database views (requires migration, schema changes)
- ⚠️ JavaScript lazy loading (may break existing functionality)

### **High Risk:**
- ❌ Pagination (major UX change)
- ❌ Partial rendering (requires frontend rewrite)
- ❌ Redis (requires server configuration)

---

## Conclusion

The dashboard can be significantly faster with minimal code changes. **Focus on Week 1 quick wins first** for maximum impact with minimum risk.

**Recommended Next Step:** Implement response caching in DashboardController (30 minutes, 50% speed improvement)

---

**Questions or Issues?** Check Laravel caching documentation: https://laravel.com/docs/cache
