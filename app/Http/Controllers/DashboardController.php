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
     * Main dashboard view - invoices grouped by employee or person code
     */
    public function index(Request $request): View
    {
        // Get filter from request or use saved preference
        $filter = $request->get('filter', session('dashboard.filter', 'overdue')); // all, overdue, unpaid

        // Get grouping preference (employee or other_ref)
        $grouping = $request->get('grouping', session('dashboard.grouping', 'employee')); // employee or other_ref

        // Save preferences to session
        if ($request->has('filter')) {
            session(['dashboard.filter' => $filter]);
        }
        if ($request->has('grouping')) {
            session(['dashboard.grouping' => $grouping]);
        }

        // Get date range, search, and comment filter parameters
        // Default: oldest invoice date to today
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $search = $request->get('search');
        $hasComments = $request->get('has_comments'); // Filter for invoices with comments
        $commentDateFilter = $request->get('comment_date_filter'); // today, 3days, week

        // Set defaults if not provided
        if (!$dateFrom && !$dateTo) {
            $oldestInvoice = \App\Models\Invoice::orderBy('invoice_date', 'asc')->first();
            $dateFrom = $oldestInvoice ? $oldestInvoice->invoice_date->format('Y-m-d') : now()->subYears(5)->format('Y-m-d');
            $dateTo = now()->format('Y-m-d');
        }

        // NEW: Check if we have database data, otherwise fall back to API
        $invoiceCount = \App\Models\Invoice::count();

        if ($invoiceCount > 0) {
            // Use database method (fast!)
            if ($grouping === 'other_ref') {
                $invoicesByEmployee = $this->invoiceService->getInvoicesByOtherRefFromDatabase(
                    $filter,
                    $dateFrom,
                    $dateTo,
                    $search,
                    $hasComments,
                    $commentDateFilter
                );
            } else {
                $invoicesByEmployee = $this->invoiceService->getInvoicesByEmployeeFromDatabase(
                    $filter,
                    $dateFrom,
                    $dateTo,
                    $search,
                    $hasComments,
                    $commentDateFilter
                );
            }
        } else {
            // Fallback to API method (for backward compatibility)
            if ($grouping === 'other_ref') {
                $invoicesByEmployee = $this->invoiceService->getInvoicesByOtherRef($filter);
            } else {
                $invoicesByEmployee = $this->invoiceService->getInvoicesByEmployee($filter);
            }
        }

        $totals = $this->invoiceService->getInvoiceTotals();
        $dataQuality = $this->invoiceService->getDataQualityStats($invoicesByEmployee);

        // NEW: Add sync information
        $syncStats = $this->invoiceService->getSyncStats();
        $lastSyncedAt = $syncStats['last_synced_at'];

        // Calculate next auto-sync time (every 6 hours from last sync)
        $nextSyncAt = null;
        if ($lastSyncedAt) {
            $nextSyncAt = $lastSyncedAt->copy()->addHours(6);
        }

        return view('dashboard.index', [
            'invoicesByEmployee' => $invoicesByEmployee,
            'totals' => $totals,
            'dataQuality' => $dataQuality,
            'lastUpdated' => now()->format('d-m-Y H:i'),
            'currentFilter' => $filter,
            'currentGrouping' => $grouping,        // NEW
            'lastSyncedAt' => $lastSyncedAt,       // NEW
            'nextSyncAt' => $nextSyncAt,           // NEW
            'syncStats' => $syncStats,             // NEW
            'usingDatabase' => $invoiceCount > 0,  // NEW
            'dateFrom' => $dateFrom,               // NEW
            'dateTo' => $dateTo,                   // NEW
            'search' => $search,                   // NEW
        ]);
    }

    /**
     * HTMX partial - refresh invoice list
     */
    public function refreshInvoices(Request $request): View
    {
        $filter = $request->get('filter', 'overdue');

        // NEW: Use database if available, otherwise API
        $invoiceCount = \App\Models\Invoice::count();

        if ($invoiceCount > 0) {
            // Database method (no cache to clear)
            $invoicesByEmployee = $this->invoiceService->getInvoicesByEmployeeFromDatabase($filter);
        } else {
            // API method (clear cache first)
            $this->invoiceService->clearCache();
            $invoicesByEmployee = $this->invoiceService->getInvoicesByEmployee($filter);
        }

        return view('dashboard.partials.invoice-list', [
            'invoicesByEmployee' => $invoicesByEmployee,
            'currentFilter' => $filter,
        ]);
    }

    /**
     * NEW: Manual sync endpoint
     */
    public function syncInvoices(Request $request): \Illuminate\Http\JsonResponse
    {
        // Increase execution time for this endpoint
        set_time_limit(300); // 5 minutes

        try {
            $testLimit = $request->get('test_limit'); // Optional test limit
            $stats = $this->invoiceService->syncAllInvoices($testLimit ? (int)$testLimit : null);

            return response()->json([
                'success' => true,
                'message' => 'Sync completed successfully',
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            \Log::error('Manual sync failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * NEW: Get sync progress
     */
    public function getSyncProgress(Request $request): \Illuminate\Http\JsonResponse
    {
        $progress = $this->invoiceService->getSyncProgress();

        if (!$progress) {
            return response()->json([
                'status' => 'idle',
                'percentage' => 0,
                'message' => 'No sync in progress',
            ]);
        }

        return response()->json($progress);
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

    /**
     * Stats page with all statistics and charts
     */
    public function stats(Request $request): View
    {
        $filter = $request->get('filter', session('dashboard.filter', 'overdue'));

        // Get date range and search parameters
        // Default: oldest invoice date to today
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $search = $request->get('search');

        // Set defaults if not provided
        if (!$dateFrom && !$dateTo) {
            $oldestInvoice = \App\Models\Invoice::orderBy('invoice_date', 'asc')->first();
            $dateFrom = $oldestInvoice ? $oldestInvoice->invoice_date->format('Y-m-d') : now()->subYears(5)->format('Y-m-d');
            $dateTo = now()->format('Y-m-d');
        }

        // Check if we have database data, otherwise fall back to API
        $invoiceCount = \App\Models\Invoice::count();

        if ($invoiceCount > 0) {
            // Use database method (fast and includes ALL invoices!)
            $invoicesByEmployee = $this->invoiceService->getInvoicesByEmployeeFromDatabase(
                $filter,
                $dateFrom,
                $dateTo,
                $search
            );
        } else {
            // Fallback to API method (for backward compatibility)
            $invoicesByEmployee = $this->invoiceService->getInvoicesByEmployee($filter);
        }

        $totals = $this->invoiceService->getInvoiceTotals();

        // Add sync information
        $syncStats = $this->invoiceService->getSyncStats();

        return view('dashboard.stats', [
            'invoicesByEmployee' => $invoicesByEmployee,
            'totals' => $totals,
            'lastUpdated' => now()->format('d-m-Y H:i'),
            'currentFilter' => $filter,
            'usingDatabase' => $invoiceCount > 0,
            'syncStats' => $syncStats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'search' => $search,
        ]);
    }
}
