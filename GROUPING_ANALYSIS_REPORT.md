# Invoice Grouping Analysis Report

**Date:** January 7, 2026
**Total Invoices Analyzed:** 22,540

---

## Executive Summary

✅ **We can create multiple grouping tabs!**

### Proposed Grouping Structure:

1. **By Employee** (Current - 6 employees)
2. **By Sales Rep Code** (New - 9 sales reps via external_reference)
3. **By WooCommerce Site** (New - BV vs BF sites)
4. **Ungrouped/Other** (Remaining invoices)

---

## Current Grouping: By Employee (salesPerson field)

**Status:** ✅ Already implemented

- Uses `employee_number` from API
- Only **~4.4%** of invoices have this field
- **6 employees** with invoices: [1, 3, 4, 5, 6, 7]
- **~95.6%** go to "Unassigned"

---

## Proposed Tab 1: Group by Sales Rep Code (external_reference)

**Data Source:** `external_reference` field (person initials)

### Distribution:

| Code | Name | Invoice Count | Percentage |
|------|------|---------------|------------|
| **MB** | Michael Binder | 321 | 1.42% |
| **MW** | Michael Wichmann | 222 | 0.98% |
| **BC** | Brian Christiansen | 199 | 0.88% |
| **AKS** | Anne Karin Skøtt | 179 | 0.79% |
| **LH** | Lone Holgersen | 140 | 0.62% |
| **LNJ** | Lars Nørby Jessen | 53 | 0.24% |
| **EKL** | Emil Kremer Lildballe | 7 | 0.03% |
| **JEN** | Jakob Erik Nielsen | 2 | 0.01% |
| **JS** | Unknown (found in data) | 8 | 0.04% |

**Total:** 1,123 invoices (4.98%)

### Important Notes:

1. **NOT the same as employee field:**
   - These codes are stored in `external_reference`
   - Employee field is often `NULL` even when person code exists
   - Example: Invoice #9981 has `MB` code but employee is "Lars Nørby Jessen #1"

2. **Pattern:** Exact match of 2-3 letter codes (e.g., `MB`, `AKS`, `LNJ`)

3. **Overlap with Employee grouping:**
   - Some invoices have BOTH employee number AND person code
   - They would appear in both tabs (which is fine)

---

## Proposed Tab 2: Group by WooCommerce Site

**Data Source:** `external_reference` field (BV-WO-xxxxx or BF-WO-xxxxx pattern)

### Distribution:

| Site | Pattern | Invoice Count | Percentage |
|------|---------|---------------|------------|
| **BilligVentilation.dk** | BV-WO-xxxxx | 6,348 | 28.16% |
| **BilligFilter.dk** | BF-WO-xxxxx | 1,010 | 4.48% |

**Total:** 7,358 invoices (32.64%)

### Implementation:

Already partially implemented - we have the links working, just need to add this as a grouping option.

---

## Remaining Invoices: "Other" Category

**Total:** 13,976 invoices (62.00%)

### Top Patterns:

These appear to be order/project numbers:
- `104754 - 6...` (55 times)
- `106144 - 6...` (42 times)
- `BM-3300003` (legacy orders)
- `BV51701` (non-WooCommerce BV orders)
- Many unique one-off references

---

## Proposed Tab Structure

### Tab 1: **By Employee** (Existing)
- Groups by `employee_number` from API
- Shows 6 employees + "Unassigned"
- **Coverage:** ~4.4% of invoices

### Tab 2: **By Sales Rep** (New)
- Groups by person codes in `external_reference`
- Shows 9 sales reps: MB, MW, BC, AKS, LH, LNJ, EKL, JEN, JS
- **Coverage:** ~5% of invoices

### Tab 3: **By WooCommerce Site** (New)
- Groups by WooCommerce order pattern
- Shows: BilligVentilation.dk, BilligFilter.dk
- **Coverage:** ~32.6% of invoices

### Tab 4: **All Other** (New)
- Everything else not matching above patterns
- Shows as one big list or further subdivided
- **Coverage:** ~62% of invoices

---

## Overlap Analysis

**Note:** Some invoices will appear in multiple tabs!

Example:
- Invoice #10328 has:
  - `external_reference = "MW"` → Shows in "Sales Rep" tab
  - `employee_number = 6` (Michael Wichmann) → Shows in "Employee" tab
  - It's the same person, but tracked via different fields

**This is OK!** Different ways to view the same data.

---

## Implementation Recommendation

### Option A: Separate Navigation Tabs
```
[By Employee] [By Sales Rep] [By Site] [All Other]
```

### Option B: Dropdown Filter (Simpler)
```
Group by: [Employee ▼]
  - Employee (Current)
  - Sales Rep Code
  - WooCommerce Site
  - No Grouping
```

### Option C: Multiple Filters (Most Flexible)
```
Primary Group: [Employee ▼]
Secondary Filter: [All ▼]
  - Show only WooCommerce orders
  - Show only Sales Rep coded
  - Show all
```

---

## Code Mapping for Sales Rep Tab

```php
$salesRepMapping = [
    'LH' => 'Lone Holgersen',
    'AKS' => 'Anne Karin Skøtt',
    'MB' => 'Michael Binder',
    'MW' => 'Michael Wichmann',
    'EKL' => 'Emil Kremer Lildballe',
    'BC' => 'Brian Christiansen',
    'LNJ' => 'Lars Nørby Jessen',
    'DH' => 'Dorte Hindahl',
    'JEN' => 'Jakob Erik Nielsen',
    'JS' => 'Unknown', // Found in data but not in your list
];
```

**Note:** `DH` (Dorte Hindahl) is in your mapping but has **0 invoices** in the current dataset.

---

## Next Steps

1. **Decision:** Which tab structure do you prefer? (A, B, or C)
2. **Implementation:** Update DashboardController and views
3. **Database:** Add query scopes to Invoice model for new groupings
4. **UI:** Add tab navigation or dropdown filters

---

## Summary

✅ **YES, we can group by person codes** (1,123 invoices)
✅ **YES, we can group by WooCommerce site** (7,358 invoices)
✅ **Coverage combined:** ~38% of invoices can be grouped
⚠️ **Remaining:** ~62% are "other" patterns (project/order numbers)

**Recommendation:** Implement all 3 grouping options as tabs or filters for maximum flexibility!
