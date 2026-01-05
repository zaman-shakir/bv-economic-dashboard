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

        return [
            'invoiceNumber' => $invoice['bookedInvoiceNumber'],
            'kundenr' => $invoice['customer']['customerNumber'] ?? null,
            'kundenavn' => $invoice['recipient']['name'] ?? 'Unknown customer',
            'overskrift' => $invoice['notes']['heading'] ?? '',
            'beloeb' => $invoice['grossAmount'],
            'remainder' => $invoice['remainder'],
            'currency' => $invoice['currency'],
            'eksterntId' => $invoice['references']['other'] ?? null,
            'date' => $invoice['date'],
            'dueDate' => $invoice['dueDate'],
            'daysOverdue' => $daysOverdue,
            'daysTillDue' => $daysTillDue,
            'status' => $status,
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
}
