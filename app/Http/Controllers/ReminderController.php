<?php

namespace App\Http\Controllers;

use App\Mail\EmployeeReminderMail;
use App\Mail\InvoiceReminderMail;
use App\Models\InvoiceReminder;
use App\Services\EconomicInvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ReminderController extends Controller
{
    public function __construct(
        protected EconomicInvoiceService $invoiceService
    ) {}

    /**
     * Display the notification center with all sent reminders
     */
    public function index(): View
    {
        $reminders = InvoiceReminder::with('sentBy:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $stats = [
            'total' => InvoiceReminder::count(),
            'sent' => InvoiceReminder::where('email_sent', true)->count(),
            'failed' => InvoiceReminder::where('email_sent', false)->count(),
            'today' => InvoiceReminder::whereDate('created_at', today())->count(),
            'this_week' => InvoiceReminder::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        return view('reminders.index', [
            'reminders' => $reminders,
            'stats' => $stats,
        ]);
    }

    /**
     * Send a reminder email for an overdue invoice
     */
    public function sendReminder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_number' => 'required|integer',
            'customer_number' => 'required|integer',
        ]);

        // Check if a reminder was sent in the last 7 days (prevent spam)
        $recentReminder = InvoiceReminder::where('invoice_number', $validated['invoice_number'])
            ->where('email_sent', true)
            ->where('created_at', '>=', now()->subDays(7))
            ->first();

        if ($recentReminder) {
            return response()->json([
                'success' => false,
                'message' => __('dashboard.reminder_sent_recently', [
                    'days' => now()->diffInDays($recentReminder->created_at)
                ]),
            ], 422);
        }

        try {
            // Fetch customer email from E-conomic API
            $customerEmail = $this->getCustomerEmail($validated['customer_number']);

            if (!$customerEmail) {
                return response()->json([
                    'success' => false,
                    'message' => __('dashboard.customer_email_not_found'),
                ], 404);
            }

            // Get invoice details from the service
            $invoices = $this->invoiceService->getAllInvoices();
            $invoice = $invoices->firstWhere('bookedInvoiceNumber', $validated['invoice_number']);

            if (!$invoice) {
                return response()->json([
                    'success' => false,
                    'message' => __('dashboard.invoice_not_found'),
                ], 404);
            }

            // Format invoice data
            $formattedInvoice = $this->invoiceService->formatInvoice($invoice);

            // Send email
            $locale = app()->getLocale();
            Mail::to($customerEmail)->send(new InvoiceReminderMail(
                $formattedInvoice,
                $invoice['recipient']['name'] ?? 'Customer',
                $locale
            ));

            // Log the reminder
            InvoiceReminder::create([
                'invoice_number' => $validated['invoice_number'],
                'customer_email' => $customerEmail,
                'customer_name' => $invoice['recipient']['name'] ?? 'Unknown',
                'amount_due' => $formattedInvoice['remainder'],
                'sent_by' => auth()->id(),
                'email_sent' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('dashboard.reminder_sent_successfully'),
            ]);

        } catch (\Exception $e) {
            // Log failed attempt
            InvoiceReminder::create([
                'invoice_number' => $validated['invoice_number'],
                'customer_email' => $customerEmail ?? 'unknown',
                'customer_name' => $invoice['recipient']['name'] ?? 'Unknown',
                'amount_due' => $formattedInvoice['remainder'] ?? 0,
                'sent_by' => auth()->id(),
                'email_sent' => false,
                'email_error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('dashboard.reminder_send_failed') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customer email from E-conomic API
     */
    protected function getCustomerEmail(int $customerNumber): ?string
    {
        // In demo mode, return a test email
        if (config('e-conomic.app_secret_token') === 'demo') {
            return 'customer@example.com';
        }

        try {
            $response = Http::withHeaders([
                'X-AppSecretToken' => config('e-conomic.app_secret_token'),
                'X-AgreementGrantToken' => config('e-conomic.agreement_grant_token'),
                'Content-Type' => 'application/json',
            ])->get("https://restapi.e-conomic.com/customers/{$customerNumber}");

            if ($response->successful()) {
                $customer = $response->json();
                return $customer['email'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get reminder history for an invoice
     */
    public function getReminderHistory(string $invoiceNumber): JsonResponse
    {
        $reminders = InvoiceReminder::where('invoice_number', $invoiceNumber)
            ->with('sentBy:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'reminders' => $reminders,
        ]);
    }

    /**
     * Send a reminder email to employee about their overdue invoices
     */
    public function sendEmployeeReminder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_number' => 'required|integer',
        ]);

        try {
            // Get employee's overdue invoices
            $overdueByEmployee = $this->invoiceService->getInvoicesByEmployee('overdue');
            $employeeData = $overdueByEmployee->firstWhere('employeeNumber', $validated['employee_number']);

            if (!$employeeData) {
                return response()->json([
                    'success' => false,
                    'message' => __('dashboard.no_overdue_for_employee'),
                ], 404);
            }

            // Fetch employee email from E-conomic API
            $employeeEmail = $this->getEmployeeEmail($validated['employee_number']);

            if (!$employeeEmail) {
                return response()->json([
                    'success' => false,
                    'message' => __('dashboard.employee_email_not_found'),
                ], 404);
            }

            // Send email
            $locale = app()->getLocale();
            Mail::to($employeeEmail)->send(new EmployeeReminderMail(
                $employeeData['employeeName'],
                $employeeData,
                $locale
            ));

            return response()->json([
                'success' => true,
                'message' => __('dashboard.employee_reminder_sent'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('dashboard.reminder_send_failed') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get employee email from E-conomic API
     */
    protected function getEmployeeEmail(int $employeeNumber): ?string
    {
        // In demo mode, return a test email
        if (config('e-conomic.app_secret_token') === 'demo') {
            return 'employee@billigventilation.dk';
        }

        try {
            $response = Http::withHeaders([
                'X-AppSecretToken' => config('e-conomic.app_secret_token'),
                'X-AgreementGrantToken' => config('e-conomic.agreement_grant_token'),
                'Content-Type' => 'application/json',
            ])->get("https://restapi.e-conomic.com/employees/{$employeeNumber}");

            if ($response->successful()) {
                $employee = $response->json();
                return $employee['email'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
