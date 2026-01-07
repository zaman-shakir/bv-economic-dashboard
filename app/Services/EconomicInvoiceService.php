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
     * Check if we're in demo mode
     */
    protected function isDemoMode(): bool
    {
        return config('e-conomic.app_secret_token') === 'demo';
    }

    /**
     * Get mock/demo invoices for testing
     */
    protected function getMockInvoices(): Collection
    {
        return collect([
            // Overdue invoices
            [
                'bookedInvoiceNumber' => 10001,
                'date' => '2025-11-15',
                'dueDate' => '2025-12-01',
                'currency' => 'DKK',
                'grossAmount' => 25000.00,
                'remainder' => 25000.00,
                'customer' => ['customerNumber' => 1001],
                'recipient' => ['name' => 'Restaurant Nordic A/S'],
                'notes' => ['heading' => 'Order 2547 - Industrial ventilation system'],
                'references' => [
                    'salesPerson' => ['employeeNumber' => 3, 'name' => 'Jesper Nielsen'],
                    'other' => 'WC-2547'
                ],
                'pdf' => ['download' => 'https://example.com/invoice.pdf']
            ],
            [
                'bookedInvoiceNumber' => 10002,
                'date' => '2025-11-20',
                'dueDate' => '2025-12-05',
                'currency' => 'DKK',
                'grossAmount' => 15000.00,
                'remainder' => 15000.00,
                'customer' => ['customerNumber' => 1002],
                'recipient' => ['name' => 'Copenhagen Hotels ApS'],
                'notes' => ['heading' => 'Order 2589 - Kitchen exhaust fan replacement'],
                'references' => [
                    'salesPerson' => ['employeeNumber' => 3, 'name' => 'Jesper Nielsen'],
                    'other' => 'WC-2589'
                ],
                'pdf' => ['download' => 'https://example.com/invoice.pdf']
            ],
            [
                'bookedInvoiceNumber' => 10003,
                'date' => '2025-10-10',
                'dueDate' => '2025-11-01',
                'currency' => 'DKK',
                'grossAmount' => 45000.00,
                'remainder' => 45000.00,
                'customer' => ['customerNumber' => 1003],
                'recipient' => ['name' => 'Office Solutions Denmark'],
                'notes' => ['heading' => 'HVAC system installation - Building 3'],
                'references' => [
                    'salesPerson' => ['employeeNumber' => 3, 'name' => 'Jesper Nielsen'],
                    'other' => null
                ],
                'pdf' => ['download' => 'https://example.com/invoice.pdf']
            ],
            [
                'bookedInvoiceNumber' => 10004,
                'date' => '2025-11-25',
                'dueDate' => '2025-12-10',
                'currency' => 'DKK',
                'grossAmount' => 12000.00,
                'remainder' => 12000.00,
                'customer' => ['customerNumber' => 1004],
                'recipient' => ['name' => 'Retail Chain Denmark A/S'],
                'notes' => ['heading' => 'Order 2601 - Air conditioning maintenance'],
                'references' => [
                    'salesPerson' => ['employeeNumber' => 7, 'name' => 'Maria Hansen'],
                    'other' => 'WC-2601'
                ],
                'pdf' => ['download' => 'https://example.com/invoice.pdf']
            ],
            [
                'bookedInvoiceNumber' => 10005,
                'date' => '2025-11-01',
                'dueDate' => '2025-11-20',
                'currency' => 'DKK',
                'grossAmount' => 8500.00,
                'remainder' => 8500.00,
                'customer' => ['customerNumber' => 1005],
                'recipient' => ['name' => 'Manufacturing Co. ApS'],
                'notes' => ['heading' => 'Order 2534 - Ventilation ducts and filters'],
                'references' => [
                    'salesPerson' => ['employeeNumber' => 7, 'name' => 'Maria Hansen'],
                    'other' => 'WC-2534'
                ],
                'pdf' => ['download' => 'https://example.com/invoice.pdf']
            ],
            // Unpaid but not yet overdue
            [
                'bookedInvoiceNumber' => 10006,
                'date' => '2025-12-20',
                'dueDate' => '2026-01-10',
                'currency' => 'DKK',
                'grossAmount' => 18000.00,
                'remainder' => 18000.00,
                'customer' => ['customerNumber' => 1006],
                'recipient' => ['name' => 'Tech Startup ApS'],
                'notes' => ['heading' => 'Order 2612 - Office ventilation upgrade'],
                'references' => [
                    'salesPerson' => ['employeeNumber' => 3, 'name' => 'Jesper Nielsen'],
                    'other' => 'WC-2612'
                ],
                'pdf' => ['download' => 'https://example.com/invoice.pdf']
            ],
            [
                'bookedInvoiceNumber' => 10007,
                'date' => '2025-12-28',
                'dueDate' => '2026-01-15',
                'currency' => 'DKK',
                'grossAmount' => 9500.00,
                'remainder' => 9500.00,
                'customer' => ['customerNumber' => 1007],
                'recipient' => ['name' => 'Fitness Center A/S'],
                'notes' => ['heading' => 'Gym ventilation system maintenance'],
                'references' => [
                    'salesPerson' => ['employeeNumber' => 7, 'name' => 'Maria Hansen'],
                    'other' => null
                ],
                'pdf' => ['download' => 'https://example.com/invoice.pdf']
            ],
            // Paid invoices
            [
                'bookedInvoiceNumber' => 10008,
                'date' => '2025-11-10',
                'dueDate' => '2025-11-25',
                'currency' => 'DKK',
                'grossAmount' => 32000.00,
                'remainder' => 0.00,
                'customer' => ['customerNumber' => 1008],
                'recipient' => ['name' => 'Shopping Mall Denmark'],
                'notes' => ['heading' => 'Order 2520 - Central air conditioning service'],
                'references' => [
                    'salesPerson' => ['employeeNumber' => 3, 'name' => 'Jesper Nielsen'],
                    'other' => 'WC-2520'
                ],
                'pdf' => ['download' => 'https://example.com/invoice.pdf']
            ],
            [
                'bookedInvoiceNumber' => 10009,
                'date' => '2025-12-01',
                'dueDate' => '2025-12-15',
                'currency' => 'DKK',
                'grossAmount' => 14500.00,
                'remainder' => 0.00,
                'customer' => ['customerNumber' => 1009],
                'recipient' => ['name' => 'School District Copenhagen'],
                'notes' => ['heading' => 'Educational facility HVAC inspection'],
                'references' => [
                    'salesPerson' => ['employeeNumber' => 7, 'name' => 'Maria Hansen'],
                    'other' => 'WC-2555'
                ],
                'pdf' => ['download' => 'https://example.com/invoice.pdf']
            ],
        ]);
    }

    /**
     * Get all overdue invoices
     * Limited to configured months for performance
     * Note: e-conomic API doesn't support date filters on /overdue endpoint,
     * so we fetch all invoices and filter client-side
     */
    public function getOverdueInvoices(): Collection
    {
        // Return mock data if in demo mode
        if ($this->isDemoMode()) {
            return $this->getMockInvoices();
        }

        $cacheDuration = config('e-conomic.cache_duration', 30) * 60; // Convert minutes to seconds

        return Cache::remember('overdue_invoices', $cacheDuration, function () {
            // Get all invoices with date filter, then filter to overdue only
            $allInvoices = $this->getAllInvoices();

            // Filter to only overdue invoices (remainder > 0 and past due date)
            return $allInvoices->filter(function ($invoice) {
                $isDue = isset($invoice['dueDate']) && $invoice['dueDate'] < now()->format('Y-m-d');
                $hasRemainder = isset($invoice['remainder']) && $invoice['remainder'] > 0;
                return $isDue && $hasRemainder;
            });
        });
    }

    /**
     * Get all unpaid invoices (includes not-yet-overdue)
     * Limited to configured months for performance
     * Note: e-conomic API doesn't support date filters on /unpaid endpoint,
     * so we fetch all invoices and filter client-side
     */
    public function getUnpaidInvoices(): Collection
    {
        // Return mock data if in demo mode
        if ($this->isDemoMode()) {
            return $this->getMockInvoices()->filter(fn($inv) => $inv['remainder'] > 0);
        }

        $cacheDuration = config('e-conomic.cache_duration', 30) * 60; // Convert minutes to seconds

        return Cache::remember('unpaid_invoices', $cacheDuration, function () {
            // Get all invoices with date filter, then filter to unpaid only
            $allInvoices = $this->getAllInvoices();

            // Filter to only unpaid invoices (remainder > 0)
            return $allInvoices->filter(function ($invoice) {
                return isset($invoice['remainder']) && $invoice['remainder'] > 0;
            });
        });
    }

    /**
     * Get all booked invoices (paid and unpaid)
     * Limited to configured months for performance
     */
    public function getAllInvoices(): Collection
    {
        // Return all mock data if in demo mode
        if ($this->isDemoMode()) {
            return $this->getMockInvoices();
        }

        $cacheDuration = config('e-conomic.cache_duration', 30) * 60; // Convert minutes to seconds

        return Cache::remember('all_invoices', $cacheDuration, function () {
            $invoices = collect();

            // PERFORMANCE FIX: Only fetch invoices from configured months
            // This prevents loading 21,000+ invoices which freezes the browser
            $months = config('e-conomic.sync_months', 6);
            $dateFrom = now()->subMonths($months)->format('Y-m-d');
            $url = "{$this->baseUrl}/invoices/booked?pagesize=1000&filter=date\$gte:{$dateFrom}";

            $response = Http::withHeaders($this->headers)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $invoices = $invoices->merge($data['collection'] ?? []);
            }

            return $invoices;
        });
    }

    /**
     * Get invoices grouped by salesperson/employee
     *
     * @param string $filter 'all', 'overdue', or 'unpaid'
     */
    public function getInvoicesByEmployee(string $filter = 'overdue'): Collection
    {
        $invoices = match($filter) {
            'all' => $this->getAllInvoices(),
            'unpaid' => $this->getUnpaidInvoices(),
            default => $this->getOverdueInvoices(),
        };

        // Track invoices without salesPerson for logging
        $totalInvoices = $invoices->count();
        $unassignedCount = $invoices->filter(function ($invoice) {
            return !isset($invoice['references']['salesPerson']);
        })->count();

        // Log warning if most invoices are unassigned
        if ($unassignedCount > 0) {
            $percentage = round(($unassignedCount / $totalInvoices) * 100);
            \Log::warning("E-conomic Data Quality: {$unassignedCount} out of {$totalInvoices} invoices ({$percentage}%) have no salesperson assigned.", [
                'filter' => $filter,
                'total_invoices' => $totalInvoices,
                'unassigned_count' => $unassignedCount,
                'suggestion' => 'Assign salespeople to invoices in e-conomic dashboard for better tracking'
            ]);
        }

        return $invoices->groupBy(function ($invoice) {
            return $invoice['references']['salesPerson']['employeeNumber'] ?? 'unassigned';
        })->map(function ($group, $employeeNumber) {
            return [
                'employeeNumber' => $employeeNumber,
                'employeeName' => $this->getEmployeeName($employeeNumber),
                'invoiceCount' => $group->count(),
                'totalAmount' => $group->sum('grossAmount'),
                'totalRemainder' => $group->sum('remainder'),
                'invoices' => $group->map(fn($inv) => $this->formatInvoice($inv))->sortByDesc('daysOverdue'),
            ];
        });
    }

    /**
     * Get overdue invoices grouped by salesperson/employee
     * @deprecated Use getInvoicesByEmployee('overdue') instead
     */
    public function getOverdueByEmployee(): Collection
    {
        return $this->getInvoicesByEmployee('overdue');
    }

    /**
     * Get invoices grouped by external reference (other ref) field
     *
     * This groups invoices by ALL values in external_reference field including:
     * - Person codes (LH, AKS, MB, etc.)
     * - WooCommerce orders (BV-WO-xxxxx, BF-WO-xxxxx)
     * - Legacy orders, project numbers, etc.
     *
     * Also shows which employee (if any) is assigned to each group.
     *
     * @param string $filter 'all', 'overdue', or 'unpaid'
     */
    public function getInvoicesByOtherRef(string $filter = 'overdue'): Collection
    {
        // Person code mapping for display names
        $personCodeMapping = [
            'LH' => 'Lone Holgersen',
            'AKS' => 'Anne Karin Skøtt',
            'MB' => 'Michael Binder',
            'MW' => 'Michael Wichmann',
            'EKL' => 'Emil Kremer Lildballe',
            'BC' => 'Brian Christiansen',
            'LNJ' => 'Lars Nørby Jessen',
            'DH' => 'Dorte Hindahl',
            'JEN' => 'Jakob Erik Nielsen',
        ];

        $invoices = match($filter) {
            'all' => $this->getAllInvoices(),
            'unpaid' => $this->getUnpaidInvoices(),
            default => $this->getOverdueInvoices(),
        };

        // Group invoices by external_reference (ALL values, not just person codes)
        return $invoices->groupBy(function ($invoice) {
            $extRef = trim($invoice['references']['other'] ?? '');
            return $extRef !== '' ? $extRef : 'unassigned';
        })->map(function ($group, $otherRef) use ($personCodeMapping) {
            // Get employee info from first invoice in group (if available)
            $firstInvoice = $group->first();
            $employeeNumber = $firstInvoice['references']['salesPerson']['employeeNumber'] ?? null;
            $employeeName = $employeeNumber ? $this->getEmployeeName($employeeNumber) : null;

            // Determine display name for this group
            $upperRef = strtoupper($otherRef);
            if ($otherRef === 'unassigned') {
                $displayName = 'No External Reference';
            } elseif (isset($personCodeMapping[$upperRef])) {
                $displayName = $personCodeMapping[$upperRef] . " ({$otherRef})";
            } else {
                $displayName = $otherRef;
            }

            // Add employee info to display name if available
            if ($employeeName) {
                $displayName .= " → 👤 {$employeeName}";
            }

            return [
                'employeeNumber' => $otherRef,
                'employeeName' => $displayName,
                'actualEmployeeNumber' => $employeeNumber,
                'actualEmployeeName' => $employeeName,
                'invoiceCount' => $group->count(),
                'totalAmount' => $group->sum('grossAmount'),
                'totalRemainder' => $group->sum('remainder'),
                'invoices' => $group->map(fn($inv) => $this->formatInvoice($inv))->sortByDesc('daysOverdue'),
            ];
        });
    }

    /**
     * Get employee name by number
     */
    protected function getEmployeeName(int|string $employeeNumber): string
    {
        if ($employeeNumber === 'unassigned') {
            return 'Unassigned';
        }

        // In demo mode, use hardcoded names
        if ($this->isDemoMode()) {
            $mockEmployees = [
                3 => 'Jesper Nielsen',
                7 => 'Maria Hansen',
            ];
            return $mockEmployees[$employeeNumber] ?? "Employee #{$employeeNumber}";
        }

        return Cache::remember("employee_{$employeeNumber}", 3600, function () use ($employeeNumber) {
            $response = Http::withHeaders($this->headers)
                ->get("{$this->baseUrl}/employees/{$employeeNumber}");

            if ($response->successful()) {
                return $response->json()['name'] ?? "Employee #{$employeeNumber}";
            }

            return "Employee #{$employeeNumber}";
        });
    }

    /**
     * Format invoice for dashboard display
     */
    public function formatInvoice(array $invoice): array
    {
        $dueDate = Carbon::parse($invoice['dueDate']);
        $now = now();
        $isPaid = $invoice['remainder'] == 0;
        $isOverdue = !$isPaid && $dueDate->isPast();

        // Calculate days (positive = overdue, negative = not yet due)
        $daysOverdue = $isOverdue ? (int) $dueDate->diffInDays($now) : 0;
        $daysTillDue = !$isPaid && !$isOverdue ? (int) $now->diffInDays($dueDate) : 0;

        // Determine status
        $status = $isPaid ? 'paid' : ($isOverdue ? 'overdue' : 'unpaid');

        // Try to find invoice in database to get ID, comment count, and latest comment timestamp
        $dbInvoice = \App\Models\Invoice::where('invoice_number', $invoice['bookedInvoiceNumber'])->first();
        $invoiceId = $dbInvoice ? $dbInvoice->id : null;
        $commentCount = $invoiceId ? \App\Models\InvoiceComment::where('invoice_id', $invoiceId)->count() : 0;
        $latestComment = $invoiceId ? \App\Models\InvoiceComment::where('invoice_id', $invoiceId)
            ->orderBy('created_at', 'desc')
            ->first() : null;

        return [
            'invoiceId' => $invoiceId,
            'invoiceNumber' => $invoice['bookedInvoiceNumber'],
            'kundenr' => $invoice['customer']['customerNumber'] ?? null,
            'kundenavn' => $invoice['recipient']['name'] ?? 'Unknown customer',
            'overskrift' => $invoice['notes']['heading'] ?? '',
            'beloeb' => $invoice['grossAmount'],
            'remainder' => $invoice['remainder'],
            'currency' => $invoice['currency'],
            'eksterntId' => $invoice['references']['other'] ?? null,
            'externalId' => $invoice['externalId'] ?? null,
            'date' => $invoice['date'],
            'dueDate' => $invoice['dueDate'],
            'daysOverdue' => $daysOverdue,
            'daysTillDue' => $daysTillDue,
            'status' => $status,
            'pdfUrl' => $invoice['pdf']['download'] ?? null,
            'commentCount' => $commentCount,
            'latestCommentAt' => $latestComment ? $latestComment->created_at->format('Y-m-d H:i:s') : null,
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
     * Get data quality statistics
     * Returns information about missing salesPerson assignments
     */
    public function getDataQualityStats(Collection $invoicesByEmployee): array
    {
        $unassignedGroup = $invoicesByEmployee->get('unassigned');

        if (!$unassignedGroup) {
            return [
                'has_unassigned' => false,
                'unassigned_count' => 0,
                'total_count' => $invoicesByEmployee->sum('invoiceCount'),
                'percentage' => 0,
            ];
        }

        $unassignedCount = $unassignedGroup['invoiceCount'] ?? 0;
        $totalCount = $invoicesByEmployee->sum('invoiceCount');
        $percentage = $totalCount > 0 ? round(($unassignedCount / $totalCount) * 100) : 0;

        return [
            'has_unassigned' => true,
            'unassigned_count' => $unassignedCount,
            'total_count' => $totalCount,
            'percentage' => $percentage,
            'message' => "{$unassignedCount} out of {$totalCount} invoices ({$percentage}%) have no salesperson assigned.",
            'suggestion' => 'Assign salespeople to invoices in your e-conomic dashboard for better tracking.',
        ];
    }

    /**
     * Clear all cached data
     */
    public function clearCache(): void
    {
        Cache::forget('overdue_invoices');
        Cache::forget('unpaid_invoices');
        Cache::forget('all_invoices');
        Cache::forget('invoice_totals');
    }

    /**
     * Sync all invoices from E-conomic API to database
     * Fetches in chunks to avoid timeouts
     *
     * @param int|null $testLimit Optional limit for testing (e.g., 100)
     * @return array Sync statistics
     */
    public function syncAllInvoices(?int $testLimit = null): array
    {
        $stats = [
            'total_fetched' => 0,
            'total_created' => 0,
            'total_updated' => 0,
            'total_pages' => 0,
            'errors' => [],
            'started_at' => now()->toIso8601String(),
        ];

        $pageNumber = 0;
        $pageSize = 1000;
        $hasMore = true;

        // NEW: Initialize progress tracking
        $this->updateSyncProgress(0, 0, 'Starting sync...', 'running');

        \Log::info("Starting invoice sync from E-conomic API", ['test_limit' => $testLimit]);

        while ($hasMore) {
            try {
                // Build URL for this page
                $url = "{$this->baseUrl}/invoices/booked";
                $url .= "?pagesize={$pageSize}&skippages={$pageNumber}";

                // Add test limit if provided
                if ($testLimit && $stats['total_fetched'] >= $testLimit) {
                    \Log::info("Test limit reached", ['limit' => $testLimit]);
                    break;
                }

                \Log::info("Fetching page {$pageNumber} from E-conomic", ['url' => $url]);

                // Fetch invoices with increased timeout
                $response = Http::timeout(60)
                               ->withHeaders($this->headers)
                               ->get($url);

                if ($response->successful()) {
                    $data = $response->json();
                    $invoices = $data['collection'] ?? [];

                    \Log::info("Received " . count($invoices) . " invoices on page {$pageNumber}");

                    // Save each invoice to database with transaction safety
                    foreach ($invoices as $invoiceData) {
                        if ($testLimit && $stats['total_fetched'] >= $testLimit) {
                            break;
                        }

                        try {
                            \DB::transaction(function () use ($invoiceData, &$stats) {
                                $invoice = \App\Models\Invoice::createOrUpdateFromApi($invoiceData);

                                if ($invoice->wasRecentlyCreated) {
                                    $stats['total_created']++;
                                } else {
                                    $stats['total_updated']++;
                                }

                                $stats['total_fetched']++;
                            });
                        } catch (\Exception $e) {
                            $stats['errors'][] = "Failed to save invoice {$invoiceData['bookedInvoiceNumber']}: " . $e->getMessage();
                            \Log::error("Failed to save invoice", [
                                'invoice_number' => $invoiceData['bookedInvoiceNumber'],
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    $stats['total_pages']++;

                    // NEW: Update progress after each page
                    $estimatedTotal = $testLimit ?? 21500; // Estimate 21.5k total or use test limit
                    $progress = min(100, ($stats['total_fetched'] / $estimatedTotal) * 100);
                    $this->updateSyncProgress(
                        $progress,
                        $stats['total_fetched'],
                        "Fetched {$stats['total_fetched']} invoices (Page {$stats['total_pages']})...",
                        'running'
                    );

                    // Check if there are more pages
                    $hasMore = count($invoices) === $pageSize && (!$testLimit || $stats['total_fetched'] < $testLimit);

                    if ($hasMore) {
                        $pageNumber++;

                        // Rate limiting: wait 100ms between requests
                        usleep(100000); // 0.1 seconds
                    }

                } else {
                    $errorMsg = "API request failed on page {$pageNumber}: HTTP " . $response->status();
                    $stats['errors'][] = $errorMsg;
                    \Log::error($errorMsg, ['response' => $response->body()]);
                    $hasMore = false;
                }

            } catch (\Exception $e) {
                $errorMsg = "Exception on page {$pageNumber}: " . $e->getMessage();
                $stats['errors'][] = $errorMsg;
                \Log::error($errorMsg, ['exception' => $e]);
                $hasMore = false;
            }
        }

        $stats['completed_at'] = now()->toIso8601String();
        $stats['duration_seconds'] = now()->diffInSeconds(Carbon::parse($stats['started_at']));

        // NEW: Populate employee names for all invoices with employee numbers
        $this->updateSyncProgress(100, $stats['total_fetched'], 'Populating employee names...', 'running');
        $this->populateEmployeeNames();

        // NEW: Mark sync as completed
        $this->updateSyncProgress(100, $stats['total_fetched'], 'Sync completed!', 'completed');

        \Log::info("Invoice sync completed", $stats);

        return $stats;
    }

    /**
     * Get invoices from database (replaces API calls)
     * Uses cursor for memory efficiency with large datasets
     *
     * @param string $filter 'all', 'overdue', or 'unpaid'
     * @return Collection
     */
    public function getInvoicesFromDatabase(string $filter = 'overdue'): Collection
    {
        $query = \App\Models\Invoice::query();

        // Apply filter
        switch ($filter) {
            case 'overdue':
                $query->overdue();
                break;
            case 'unpaid':
                $query->unpaid();
                break;
            case 'all':
                // No filter, get all
                break;
        }

        // Use cursor for memory efficiency with large datasets
        return $query->orderBy('due_date', 'asc')->cursor()->map(function ($invoice) {
            // Get comment count for this invoice
            $commentCount = \App\Models\InvoiceComment::where('invoice_id', $invoice->id)->count();

            return [
                'invoiceId' => $invoice->id,
                'invoiceNumber' => $invoice->invoice_number,
                'kundenr' => $invoice->customer_number,
                'kundenavn' => $invoice->customer_name,
                'overskrift' => $invoice->subject,
                'beloeb' => $invoice->gross_amount,
                'remainder' => $invoice->remainder,
                'currency' => $invoice->currency,
                'eksterntId' => $invoice->external_reference,
                'externalId' => $invoice->external_id,
                'date' => $invoice->invoice_date->format('Y-m-d'),
                'dueDate' => $invoice->due_date->format('Y-m-d'),
                'daysOverdue' => $invoice->days_overdue,
                'daysTillDue' => $invoice->days_till_due,
                'status' => $invoice->status,
                'pdfUrl' => $invoice->pdf_url,
                'employeeNumber' => $invoice->employee_number,
                'employeeName' => $invoice->employee_name,
                'commentCount' => $commentCount,
            ];
        })->collect();
    }

    /**
     * Get invoices grouped by employee from database
     * MEMORY OPTIMIZED: Uses database aggregation instead of loading all invoices
     *
     * @param string $filter 'all', 'overdue', or 'unpaid'
     * @param string|null $dateFrom Start date for filtering (Y-m-d format)
     * @param string|null $dateTo End date for filtering (Y-m-d format)
     * @param string|null $search Search term for customer name, invoice number, external reference
     * @return Collection
     */
    public function getInvoicesByEmployeeFromDatabase(
        string $filter = 'overdue',
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $search = null,
        ?string $hasComments = null,
        ?string $commentDateFilter = null
    ): Collection
    {
        // Build base query with filter
        $baseQuery = \App\Models\Invoice::query();

        switch ($filter) {
            case 'overdue':
                $baseQuery->overdue();
                break;
            case 'unpaid':
                $baseQuery->unpaid();
                break;
            case 'all':
                // No filter
                break;
        }

        // Apply date range filter
        $baseQuery->dateRange($dateFrom, $dateTo);

        // Apply search filter
        $baseQuery->search($search);

        // Apply comment filters
        if ($hasComments === '1' || $hasComments === 'true') {
            $baseQuery->has('comments');
        }

        if ($commentDateFilter) {
            $commentDate = match($commentDateFilter) {
                'today' => now()->startOfDay(),
                '3days' => now()->subDays(3)->startOfDay(),
                'week' => now()->subWeek()->startOfDay(),
                default => null
            };

            if ($commentDate) {
                $baseQuery->whereHas('comments', function($query) use ($commentDate) {
                    $query->where('created_at', '>=', $commentDate);
                });
            }
        }

        // Get employee groupings with aggregations (MEMORY EFFICIENT!)
        $employeeGroups = (clone $baseQuery)
            ->select([
                \DB::raw('COALESCE(employee_number, "unassigned") as employee_number'),
                \DB::raw('MAX(employee_name) as employee_name'),
                \DB::raw('COUNT(*) as invoice_count'),
                \DB::raw('SUM(gross_amount) as total_amount'),
                \DB::raw('SUM(remainder) as total_remainder'),
            ])
            ->groupBy(\DB::raw('COALESCE(employee_number, "unassigned")'))
            ->get();

        // Now fetch invoices for each employee group using chunking
        return $employeeGroups->mapWithKeys(function ($group) use ($filter, $dateFrom, $dateTo, $search) {
            $employeeNumber = $group->employee_number;

            // Build query for this employee's invoices
            $invoiceQuery = \App\Models\Invoice::query();

            // Apply same filter
            switch ($filter) {
                case 'overdue':
                    $invoiceQuery->overdue();
                    break;
                case 'unpaid':
                    $invoiceQuery->unpaid();
                    break;
            }

            // Apply date range filter
            $invoiceQuery->dateRange($dateFrom, $dateTo);

            // Apply search filter
            $invoiceQuery->search($search);

            // Filter by employee
            if ($employeeNumber === 'unassigned') {
                $invoiceQuery->whereNull('employee_number');
            } else {
                $invoiceQuery->where('employee_number', $employeeNumber);
            }

            // Fetch invoices for this employee (limited to prevent memory issues)
            // Only load top 100 most critical invoices per employee for dashboard display
            $invoices = $invoiceQuery
                ->orderBy('due_date', 'asc')
                ->limit(100) // Dashboard limit: show top 100 per employee
                ->get()
                ->map(function ($invoice) {
                    // Get comment count and latest comment timestamp for this invoice
                    $commentCount = \App\Models\InvoiceComment::where('invoice_id', $invoice->id)->count();
                    $latestComment = \App\Models\InvoiceComment::where('invoice_id', $invoice->id)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    return [
                        'invoiceId' => $invoice->id,
                        'invoiceNumber' => $invoice->invoice_number,
                        'kundenr' => $invoice->customer_number,
                        'kundenavn' => $invoice->customer_name,
                        'overskrift' => $invoice->subject,
                        'beloeb' => $invoice->gross_amount,
                        'remainder' => $invoice->remainder,
                        'currency' => $invoice->currency,
                        'eksterntId' => $invoice->external_reference,
                        'externalId' => $invoice->external_id,
                        'date' => $invoice->invoice_date->format('Y-m-d'),
                        'dueDate' => $invoice->due_date->format('Y-m-d'),
                        'daysOverdue' => $invoice->days_overdue,
                        'daysTillDue' => $invoice->days_till_due,
                        'status' => $invoice->status,
                        'pdfUrl' => $invoice->pdf_url,
                        'employeeNumber' => $invoice->employee_number,
                        'employeeName' => $invoice->employee_name,
                        'commentCount' => $commentCount,
                        'latestCommentAt' => $latestComment ? $latestComment->created_at->format('Y-m-d H:i:s') : null,
                    ];
                })
                ->sortByDesc('daysOverdue')
                ->values();

            return [
                $employeeNumber => [
                    'employeeNumber' => $employeeNumber,
                    'employeeName' => $employeeNumber === 'unassigned'
                        ? 'Unassigned'
                        : ($group->employee_name ?? "Employee #{$employeeNumber}"),
                    'invoiceCount' => (int) $group->invoice_count,
                    'totalAmount' => (float) $group->total_amount,
                    'totalRemainder' => (float) $group->total_remainder,
                    'invoices' => $invoices->all(),
                ]
            ];
        });
    }

    /**
     * Get invoices grouped by external reference (other ref) from database
     *
     * This groups invoices by ALL values in external_reference field including:
     * - Person codes (LH, AKS, MB, etc.)
     * - WooCommerce orders (BV-WO-xxxxx, BF-WO-xxxxx)
     * - Legacy orders, project numbers, etc.
     *
     * Also shows which employee (if any) is assigned to each group.
     *
     * @param string $filter 'all', 'overdue', or 'unpaid'
     * @param string|null $dateFrom Start date filter (YYYY-MM-DD)
     * @param string|null $dateTo End date filter (YYYY-MM-DD)
     * @param string|null $search Search term for customer name/invoice number
     */
    public function getInvoicesByOtherRefFromDatabase(
        string $filter = 'overdue',
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $search = null,
        ?string $hasComments = null,
        ?string $commentDateFilter = null
    ): Collection
    {
        // Person code mapping for display names
        $personCodeMapping = [
            'LH' => 'Lone Holgersen',
            'AKS' => 'Anne Karin Skøtt',
            'MB' => 'Michael Binder',
            'MW' => 'Michael Wichmann',
            'EKL' => 'Emil Kremer Lildballe',
            'BC' => 'Brian Christiansen',
            'LNJ' => 'Lars Nørby Jessen',
            'DH' => 'Dorte Hindahl',
            'JEN' => 'Jakob Erik Nielsen',
        ];

        // Build base query with filter
        $baseQuery = \App\Models\Invoice::query();

        switch ($filter) {
            case 'overdue':
                $baseQuery->overdue();
                break;
            case 'unpaid':
                $baseQuery->unpaid();
                break;
            case 'all':
                // No filter
                break;
        }

        // Apply date range filter
        $baseQuery->dateRange($dateFrom, $dateTo);

        // Apply search filter
        $baseQuery->search($search);

        // Apply comment filters
        if ($hasComments === '1' || $hasComments === 'true') {
            $baseQuery->has('comments');
        }

        if ($commentDateFilter) {
            $commentDate = match($commentDateFilter) {
                'today' => now()->startOfDay(),
                '3days' => now()->subDays(3)->startOfDay(),
                'week' => now()->subWeek()->startOfDay(),
                default => null
            };

            if ($commentDate) {
                $baseQuery->whereHas('comments', function($query) use ($commentDate) {
                    $query->where('created_at', '>=', $commentDate);
                });
            }
        }

        // Group by pattern (combine similar orders into logical groups)
        $groupingLogic = "CASE
            WHEN TRIM(external_reference) LIKE 'BV-WO-%' THEN 'BV-Webordrer'
            WHEN TRIM(external_reference) LIKE 'BF-WO-%' THEN 'BF-Webordrer'
            WHEN TRIM(external_reference) LIKE 'BM-%' THEN 'BM-Orders'
            WHEN TRIM(external_reference) LIKE 'BV%' AND TRIM(external_reference) NOT LIKE 'BV-WO-%' THEN 'BV-Orders'
            WHEN TRIM(external_reference) LIKE 'BF%' AND TRIM(external_reference) NOT LIKE 'BF-WO-%' THEN 'BF-Orders'
            WHEN UPPER(TRIM(external_reference)) IN ('LH', 'AKS', 'MB', 'MW', 'EKL', 'BC', 'LNJ', 'DH', 'JEN') THEN UPPER(TRIM(external_reference))
            WHEN TRIM(external_reference) REGEXP '^[0-9]+ - [0-9]+$' THEN CONCAT('Project-', SUBSTRING_INDEX(TRIM(external_reference), ' - ', 1))
            WHEN TRIM(external_reference) = '' OR external_reference IS NULL THEN 'unassigned'
            ELSE TRIM(external_reference)
        END";

        // Get grouped references by pattern (this creates logical groups)
        $topRefs = (clone $baseQuery)
            ->select([
                \DB::raw("{$groupingLogic} as other_ref"),
                \DB::raw('MAX(employee_number) as employee_number'),
                \DB::raw('MAX(employee_name) as employee_name'),
                \DB::raw('COUNT(*) as invoice_count'),
                \DB::raw('SUM(gross_amount) as total_amount'),
                \DB::raw('SUM(remainder) as total_remainder'),
            ])
            ->groupBy(\DB::raw($groupingLogic))
            ->orderByRaw('COUNT(*) DESC')
            ->limit(50) // Show top 50 groups by invoice count
            ->get();

        // Load all invoices with their group classification
        $allInvoices = $baseQuery
            ->selectRaw("*, {$groupingLogic} as group_key")
            ->orderBy('due_date', 'asc')
            ->get();

        // Group invoices by their pattern-based group key
        $groupedInvoices = $allInvoices->groupBy('group_key');

        // Build the result collection using pre-calculated data from $topRefs
        return $topRefs->mapWithKeys(function ($refData) use ($groupedInvoices, $personCodeMapping) {
            $otherRef = $refData->other_ref;
            $invoices = $groupedInvoices->get($otherRef, collect());

            // Get employee info from aggregated data
            $employeeNumber = $refData->employee_number;
            $employeeName = $refData->employee_name;

            // Determine display name based on group type
            $upperRef = strtoupper($otherRef);
            $isPatternGroup = false;

            if ($otherRef === 'unassigned') {
                $displayName = 'No External Reference';
                $isPatternGroup = true; // Don't show employee for this catch-all group
            } elseif ($otherRef === 'BV-Webordrer') {
                $displayName = 'BilligVentilation Webordrer';
                $isPatternGroup = true;
            } elseif ($otherRef === 'BF-Webordrer') {
                $displayName = 'BilligFilter Webordrer';
                $isPatternGroup = true;
            } elseif ($otherRef === 'BM-Orders') {
                $displayName = 'BM Orders (Legacy)';
                $isPatternGroup = true;
            } elseif ($otherRef === 'BV-Orders') {
                $displayName = 'BV Orders (Non-Web)';
                $isPatternGroup = true;
            } elseif ($otherRef === 'BF-Orders') {
                $displayName = 'BF Orders (Non-Web)';
                $isPatternGroup = true;
            } elseif (strpos($otherRef, 'Project-') === 0) {
                $projectNum = str_replace('Project-', '', $otherRef);
                $displayName = "Project {$projectNum}";
                $isPatternGroup = true;
            } elseif (isset($personCodeMapping[$upperRef])) {
                $displayName = $personCodeMapping[$upperRef] . " ({$otherRef})";
            } else {
                $displayName = $otherRef;
            }

            // Add employee info to display name if available (but not for pattern groups)
            if ($employeeName && !$isPatternGroup) {
                $displayName .= " [Employee: {$employeeName}]";
            }

            // Limit to 100 invoices per group for display
            $limitedInvoices = $invoices->take(100)->map(function ($invoice) {
                // Get comment count for this invoice
                $commentCount = \App\Models\InvoiceComment::where('invoice_id', $invoice->id)->count();

                return [
                    'invoiceId' => $invoice->id,
                    'invoiceNumber' => $invoice->invoice_number,
                    'kundenr' => $invoice->customer_number,
                    'kundenavn' => $invoice->customer_name,
                    'overskrift' => $invoice->subject,
                    'beloeb' => $invoice->gross_amount,
                    'remainder' => $invoice->remainder,
                    'currency' => $invoice->currency,
                    'eksterntId' => $invoice->external_reference,
                    'externalId' => $invoice->external_id,
                    'date' => $invoice->invoice_date->format('Y-m-d'),
                    'dueDate' => $invoice->due_date->format('Y-m-d'),
                    'daysOverdue' => $invoice->days_overdue,
                    'daysTillDue' => $invoice->days_till_due,
                    'status' => $invoice->status,
                    'pdfUrl' => $invoice->pdf_url,
                    'employeeNumber' => $invoice->employee_number,
                    'employeeName' => $invoice->employee_name,
                    'commentCount' => $commentCount,
                ];
            })->sortByDesc('daysOverdue')->values();

            return [
                $otherRef => [
                    'employeeNumber' => $otherRef,
                    'employeeName' => $displayName,
                    'actualEmployeeNumber' => $employeeNumber,
                    'actualEmployeeName' => $employeeName,
                    'invoiceCount' => (int) $refData->invoice_count,
                    'totalAmount' => (float) $refData->total_amount,
                    'totalRemainder' => (float) $refData->total_remainder,
                    'invoices' => $limitedInvoices->all(),
                ]
            ];
        });
    }

    /**
     * Get last sync timestamp
     */
    public function getLastSyncTime(): ?Carbon
    {
        $lastSync = \App\Models\Invoice::max('last_synced_at');
        return $lastSync ? Carbon::parse($lastSync) : null;
    }

    /**
     * Get sync statistics
     */
    public function getSyncStats(): array
    {
        return [
            'total_invoices' => \App\Models\Invoice::count(),
            'overdue_count' => \App\Models\Invoice::overdue()->count(),
            'unpaid_count' => \App\Models\Invoice::unpaid()->count(),
            'paid_count' => \App\Models\Invoice::paid()->count(),
            'unassigned_count' => \App\Models\Invoice::unassigned()->count(),
            'last_synced_at' => $this->getLastSyncTime(),
        ];
    }

    /**
     * Update sync progress in cache
     *
     * @param float $percentage Progress percentage (0-100)
     * @param int $current Current count of invoices processed
     * @param string $message Status message
     * @param string $status Status: running, completed, failed
     */
    protected function updateSyncProgress(float $percentage, int $current, string $message, string $status): void
    {
        Cache::put('invoice_sync_progress', [
            'percentage' => round($percentage, 2),
            'current' => $current,
            'message' => $message,
            'status' => $status,
            'updated_at' => now()->toIso8601String(),
        ], 300); // Cache for 5 minutes
    }

    /**
     * Get current sync progress
     */
    public function getSyncProgress(): ?array
    {
        return Cache::get('invoice_sync_progress');
    }

    /**
     * Clear sync progress
     */
    public function clearSyncProgress(): void
    {
        Cache::forget('invoice_sync_progress');
    }

    /**
     * Populate employee names for all invoices that have employee numbers
     * Fetches employee names from E-conomic API and updates database
     */
    public function populateEmployeeNames(): void
    {
        // Get unique employee numbers that need names
        $employeeNumbers = \App\Models\Invoice::query()
            ->whereNotNull('employee_number')
            ->where(function($query) {
                $query->whereNull('employee_name')
                      ->orWhere('employee_name', '');
            })
            ->distinct()
            ->pluck('employee_number');

        if ($employeeNumbers->isEmpty()) {
            \Log::info("No employee names to populate");
            return;
        }

        \Log::info("Populating employee names for " . $employeeNumbers->count() . " employees");

        foreach ($employeeNumbers as $employeeNumber) {
            try {
                // Fetch employee name from E-conomic API (uses cache)
                $employeeName = $this->getEmployeeName($employeeNumber);

                // Update all invoices with this employee number
                \App\Models\Invoice::where('employee_number', $employeeNumber)
                    ->update(['employee_name' => $employeeName]);

                \Log::info("Updated employee #{$employeeNumber} name to: {$employeeName}");
            } catch (\Exception $e) {
                \Log::warning("Failed to fetch name for employee #{$employeeNumber}: " . $e->getMessage());
            }
        }
    }
}
