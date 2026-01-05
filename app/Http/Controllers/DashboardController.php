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
     * Main dashboard view - invoices grouped by employee
     */
    public function index(Request $request): View
    {
        // Get filter from request or use saved preference
        $filter = $request->get('filter', session('dashboard.filter', 'overdue')); // all, overdue, unpaid

        // Save filter preference to session
        if ($request->has('filter')) {
            session(['dashboard.filter' => $filter]);
        }

        $invoicesByEmployee = $this->invoiceService->getInvoicesByEmployee($filter);
        $totals = $this->invoiceService->getInvoiceTotals();
        $dataQuality = $this->invoiceService->getDataQualityStats($invoicesByEmployee);

        return view('dashboard.index', [
            'invoicesByEmployee' => $invoicesByEmployee,
            'totals' => $totals,
            'dataQuality' => $dataQuality,
            'lastUpdated' => now()->format('d-m-Y H:i'),
            'currentFilter' => $filter,
        ]);
    }

    /**
     * HTMX partial - refresh invoice list
     */
    public function refreshInvoices(Request $request): View
    {
        $filter = $request->get('filter', 'overdue');
        $this->invoiceService->clearCache();
        $invoicesByEmployee = $this->invoiceService->getInvoicesByEmployee($filter);

        return view('dashboard.partials.invoice-list', [
            'invoicesByEmployee' => $invoicesByEmployee,
            'currentFilter' => $filter,
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

    /**
     * Stats page with all statistics and charts
     */
    public function stats(Request $request): View
    {
        $filter = $request->get('filter', session('dashboard.filter', 'overdue'));
        $invoicesByEmployee = $this->invoiceService->getInvoicesByEmployee($filter);
        $totals = $this->invoiceService->getInvoiceTotals();

        return view('dashboard.stats', [
            'invoicesByEmployee' => $invoicesByEmployee,
            'totals' => $totals,
            'lastUpdated' => now()->format('d-m-Y H:i'),
            'currentFilter' => $filter,
        ]);
    }
}
