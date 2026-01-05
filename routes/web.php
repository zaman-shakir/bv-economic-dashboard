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

// Test env reading
Route::get('/test-env', function() {
    return response()->json([
        'env_direct' => env('ECONOMIC_APP_SECRET_TOKEN'),
        'config' => config('e-conomic.app_secret_token'),
        'all_economic_env' => [
            'ECONOMIC_APP_SECRET_TOKEN' => env('ECONOMIC_APP_SECRET_TOKEN'),
            'ECONOMIC_AGREEMENT_GRANT_TOKEN' => env('ECONOMIC_AGREEMENT_GRANT_TOKEN'),
        ]
    ]);
});

// Temporary route to check invoice structure (REMOVE AFTER USE)
Route::get('/check-invoices', function() {
    try {
        $appToken = config('e-conomic.app_secret_token');
        $grantToken = config('e-conomic.agreement_grant_token');

        $headers = [
            'X-AppSecretToken' => $appToken,
            'X-AgreementGrantToken' => $grantToken,
            'Content-Type' => 'application/json',
        ];

        // Get 5 most recent invoices WITHOUT date filter first (to test)
        $response = \Illuminate\Support\Facades\Http::withHeaders($headers)
            ->timeout(30)
            ->get("https://restapi.e-conomic.com/invoices/booked?pagesize=5");

        if ($response->successful()) {
            $data = $response->json();

            return response()->json([
                'message' => 'Sample of 5 most recent invoices',
                'total_invoices' => $data['pagination']['results'] ?? 0,
                'sample_invoices' => $data['collection'] ?? [],
                'instructions' => [
                    'Check if "references" field contains "salesPerson"',
                    'If missing, invoices may not have salespeople assigned in e-conomic',
                    'You may need to assign salespeople in e-conomic dashboard first'
                ]
            ], JSON_PRETTY_PRINT);
        }

        return response()->json([
            'error' => 'API request failed',
            'status_code' => $response->status(),
            'response_body' => $response->body()
        ], $response->status());

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Exception occurred',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

// Temporary log viewer route (REMOVE AFTER USE for security)
Route::get('/view-logs', function() {
    $logFile = storage_path('logs/laravel.log');

    if (!file_exists($logFile)) {
        return response()->json([
            'error' => 'Log file not found',
            'path' => $logFile
        ], 404);
    }

    // Get last 200 lines of the log file
    $lines = [];
    $file = new \SplFileObject($logFile);
    $file->seek(PHP_INT_MAX);
    $lastLine = $file->key();
    $startLine = max(0, $lastLine - 200);

    $file->seek($startLine);
    while (!$file->eof()) {
        $lines[] = $file->current();
        $file->next();
    }

    $logContent = implode('', $lines);

    // Return formatted HTML for better readability
    return response('<html>
        <head>
            <title>Laravel Logs - Last 200 Lines</title>
            <style>
                body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
                pre { white-space: pre-wrap; word-wrap: break-word; }
                .error { color: #f44747; }
                .warning { color: #ff8c00; }
                .info { color: #4ec9b0; }
                h1 { color: #4ec9b0; }
                .controls { margin-bottom: 20px; }
                .controls a { color: #4ec9b0; margin-right: 15px; }
            </style>
        </head>
        <body>
            <h1>Laravel Logs (Last 200 Lines)</h1>
            <div class="controls">
                <a href="/view-logs">Refresh Logs</a>
                <a href="/clear-all-cache">Clear Cache</a>
                <a href="/debug-api">Test API</a>
            </div>
            <pre>' . htmlspecialchars($logContent) . '</pre>
        </body>
    </html>');
});

require __DIR__.'/auth.php';
