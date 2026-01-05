<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard for authenticated users
Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware('auth');

// Dashboard routes (authenticated users only)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/refresh', [DashboardController::class, 'refreshInvoices'])->name('dashboard.refresh');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    // Reminder routes
    Route::get('/reminders', [ReminderController::class, 'index'])->name('reminders.index');
    Route::post('/reminders/send', [ReminderController::class, 'sendReminder'])->name('reminders.send');
    Route::post('/reminders/send-employee', [ReminderController::class, 'sendEmployeeReminder'])->name('reminders.send-employee');
    Route::get('/reminders/{invoiceNumber}/history', [ReminderController::class, 'getReminderHistory'])->name('reminders.history');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// User management routes (admin only)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

// API routes
Route::prefix('api')->group(function () {
    Route::get('/overdue', [DashboardController::class, 'apiOverdue']);
});

// Language switching
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Temporary cache clearing route (REMOVE AFTER USE for security)
Route::get('/clear-all-cache', function() {
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');

    return response()->json([
        'success' => true,
        'message' => 'All caches cleared successfully!',
        'cleared' => [
            'config_cache' => 'cleared',
            'application_cache' => 'cleared',
            'view_cache' => 'cleared',
            'route_cache' => 'cleared'
        ],
        'next_step' => 'Refresh your dashboard to see real data from e-conomic API'
    ]);
});

// Temporary debug route to test e-conomic API connection (REMOVE AFTER USE)
Route::get('/debug-api', function() {
    try {
        $appToken = config('e-conomic.app_secret_token');
        $grantToken = config('e-conomic.agreement_grant_token');

        // Check if tokens are loaded
        if (!$appToken || !$grantToken) {
            return response()->json([
                'error' => 'API tokens not found in configuration',
                'app_token_exists' => !empty($appToken),
                'grant_token_exists' => !empty($grantToken),
                'app_token_value' => $appToken ? substr($appToken, 0, 10) . '...' : 'null',
            ], 500);
        }

        // Check if still in demo mode
        if ($appToken === 'demo') {
            return response()->json([
                'warning' => 'Still in demo mode',
                'message' => 'Tokens are set to "demo". Config cache may not be cleared.',
                'solution' => 'Visit /clear-all-cache first'
            ]);
        }

        // Try a simple API call
        $headers = [
            'X-AppSecretToken' => $appToken,
            'X-AgreementGrantToken' => $grantToken,
            'Content-Type' => 'application/json',
        ];

        $response = \Illuminate\Support\Facades\Http::withHeaders($headers)
            ->get('https://restapi.e-conomic.com/invoices/booked?pagesize=1');

        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'message' => 'API connection successful!',
                'status_code' => $response->status(),
                'data_preview' => $response->json(),
            ]);
        } else {
            return response()->json([
                'error' => 'API request failed',
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'headers_sent' => [
                    'X-AppSecretToken' => substr($appToken, 0, 10) . '...',
                    'X-AgreementGrantToken' => substr($grantToken, 0, 10) . '...',
                ]
            ], $response->status());
        }

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Exception occurred',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

require __DIR__.'/auth.php';
