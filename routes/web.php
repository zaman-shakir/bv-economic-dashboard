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

// Temporary make user admin route (REMOVE AFTER USE for security)
Route::get('/make-admin/{email}', function($email) {
    $user = \App\Models\User::where('email', $email)->first();

    if (!$user) {
        return response()->json([
            'error' => 'User not found',
            'email' => $email
        ], 404);
    }

    $user->is_admin = true;
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'User is now an admin!',
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => $user->is_admin
        ],
        'next_step' => 'Refresh your dashboard to see the Users menu'
    ]);
});

// Temporary password reset route (REMOVE AFTER USE for security)
Route::get('/reset-password/{email}/{password}', function($email, $password) {
    $user = \App\Models\User::where('email', $email)->first();

    if (!$user) {
        return response()->json([
            'error' => 'User not found',
            'email' => $email,
            'hint' => 'Check if email is correct'
        ], 404);
    }

    $user->password = bcrypt($password);
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'Password updated successfully!',
        'user' => [
            'name' => $user->name,
            'email' => $user->email
        ],
        'new_password' => $password,
        'next_step' => 'You can now log in with your new password'
    ]);
});

// Email Testing and Debugging Tool (REMOVE AFTER USE for security)
Route::get('/test-email/{recipient?}', function($recipient = null) {
    $results = [
        'timestamp' => now()->format('Y-m-d H:i:s'),
        'config' => [],
        'connection_test' => [],
        'email_test' => [],
    ];

    // 1. Show current mail configuration
    try {
        $results['config'] = [
            'MAIL_MAILER' => config('mail.default'),
            'MAIL_HOST' => config('mail.mailers.smtp.host'),
            'MAIL_PORT' => config('mail.mailers.smtp.port'),
            'MAIL_ENCRYPTION' => config('mail.mailers.smtp.encryption'),
            'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
            'MAIL_PASSWORD' => config('mail.mailers.smtp.password') ? '***SET***' : 'NOT SET',
            'MAIL_FROM_ADDRESS' => config('mail.from.address'),
            'MAIL_FROM_NAME' => config('mail.from.name'),
        ];
    } catch (\Exception $e) {
        $results['config']['error'] = $e->getMessage();
    }

    // 2. Test SMTP connection
    try {
        $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
            config('mail.mailers.smtp.host'),
            config('mail.mailers.smtp.port'),
            config('mail.mailers.smtp.encryption') === 'tls'
        );

        if (config('mail.mailers.smtp.username')) {
            $transport->setUsername(config('mail.mailers.smtp.username'));
            $transport->setPassword(config('mail.mailers.smtp.password'));
        }

        $transport->start();

        $results['connection_test'] = [
            'status' => 'SUCCESS',
            'message' => 'SMTP connection successful!',
            'server' => config('mail.mailers.smtp.host') . ':' . config('mail.mailers.smtp.port'),
        ];

        $transport->stop();
    } catch (\Exception $e) {
        $results['connection_test'] = [
            'status' => 'FAILED',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ];
    }

    // 3. Try to send a test email
    if ($recipient) {
        try {
            \Illuminate\Support\Facades\Mail::raw('This is a test email from BilligVentilation Dashboard.', function($message) use ($recipient) {
                $message->to($recipient)
                        ->subject('Test Email - BilligVentilation Dashboard');
            });

            $results['email_test'] = [
                'status' => 'SUCCESS',
                'message' => "Test email sent successfully to {$recipient}",
                'recipient' => $recipient,
                'next_step' => 'Check your inbox (and spam folder)',
            ];
        } catch (\Exception $e) {
            $results['email_test'] = [
                'status' => 'FAILED',
                'error' => $e->getMessage(),
                'recipient' => $recipient,
                'trace' => $e->getTraceAsString(),
            ];
        }
    } else {
        $results['email_test'] = [
            'status' => 'SKIPPED',
            'message' => 'No recipient provided',
            'usage' => 'Add email to URL: /test-email/your-email@example.com',
        ];
    }

    // Return formatted HTML response
    return response('<html>
        <head>
            <title>Email Testing & Debugging</title>
            <style>
                body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
                h1 { color: #4ec9b0; }
                h2 { color: #569cd6; margin-top: 30px; }
                .success { color: #4ec9b0; }
                .error { color: #f44747; }
                .warning { color: #ff8c00; }
                pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow-x: auto; }
                .box { background: #2d2d2d; padding: 15px; margin: 10px 0; border-radius: 5px; }
                .controls { margin-bottom: 20px; }
                .controls a { color: #4ec9b0; margin-right: 15px; text-decoration: none; }
                .controls a:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <h1>📧 Email Testing & Debugging</h1>
            <div class="controls">
                <a href="/test-email">Refresh Test</a>
                <a href="/test-email/your-email@example.com">Send Test Email</a>
                <a href="/clear-all-cache">Clear Cache</a>
                <a href="/view-logs">View Logs</a>
            </div>

            <h2>1. Mail Configuration</h2>
            <div class="box">
                <pre>' . json_encode($results['config'], JSON_PRETTY_PRINT) . '</pre>
            </div>

            <h2>2. SMTP Connection Test</h2>
            <div class="box">
                ' . ($results['connection_test']['status'] === 'SUCCESS'
                    ? '<span class="success">✓ ' . $results['connection_test']['message'] . '</span>'
                    : '<span class="error">✗ Connection Failed</span>') . '
                <pre>' . json_encode($results['connection_test'], JSON_PRETTY_PRINT) . '</pre>
            </div>

            <h2>3. Send Test Email</h2>
            <div class="box">
                ' . ($results['email_test']['status'] === 'SUCCESS'
                    ? '<span class="success">✓ ' . $results['email_test']['message'] . '</span>'
                    : ($results['email_test']['status'] === 'FAILED'
                        ? '<span class="error">✗ Email Send Failed</span>'
                        : '<span class="warning">⚠ ' . $results['email_test']['message'] . '</span>')) . '
                <pre>' . json_encode($results['email_test'], JSON_PRETTY_PRINT) . '</pre>
            </div>

            <h2>Quick Fixes</h2>
            <div class="box">
                <p><strong>If MAIL_PASSWORD shows "NOT SET":</strong></p>
                <p>1. Edit .env file: MAIL_PASSWORD=your_actual_password</p>
                <p>2. Clear cache: <a href="/clear-all-cache">/clear-all-cache</a></p>

                <p style="margin-top: 15px;"><strong>If connection fails:</strong></p>
                <p>1. Check MAIL_HOST: ' . config('mail.mailers.smtp.host') . '</p>
                <p>2. Check MAIL_PORT: ' . config('mail.mailers.smtp.port') . '</p>
                <p>3. Try alternative ports: 587 (TLS) or 465 (SSL)</p>

                <p style="margin-top: 15px;"><strong>Common Issues:</strong></p>
                <p>• "Authentication failed" → Wrong username/password</p>
                <p>• "Connection refused" → Wrong host or port</p>
                <p>• "Connection timeout" → Firewall blocking port</p>
            </div>
        </body>
    </html>');
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
