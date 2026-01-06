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
    Route::post('/dashboard/sync', [DashboardController::class, 'syncInvoices'])->name('dashboard.sync'); // NEW
    Route::get('/dashboard/sync-progress', [DashboardController::class, 'getSyncProgress'])->name('dashboard.sync-progress'); // NEW

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
    $results = [];

    // Clear Laravel caches
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    $results['config_cache'] = 'cleared';

    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    $results['application_cache'] = 'cleared';

    \Illuminate\Support\Facades\Artisan::call('view:clear');
    $results['view_cache'] = 'cleared';

    \Illuminate\Support\Facades\Artisan::call('route:clear');
    $results['route_cache'] = 'cleared';

    // Clear OPcache if available (THIS IS OFTEN THE CULPRIT!)
    if (function_exists('opcache_reset')) {
        opcache_reset();
        $results['opcache'] = 'CLEARED ✓';
    } else {
        $results['opcache'] = 'not available on this server';
    }

    // Delete bootstrap cache files manually
    $bootstrapCache = base_path('bootstrap/cache/config.php');
    if (file_exists($bootstrapCache)) {
        unlink($bootstrapCache);
        $results['bootstrap_config_file'] = 'DELETED ✓';
    } else {
        $results['bootstrap_config_file'] = 'not found (good)';
    }

    // Verify .env is readable
    $envPath = base_path('.env');
    $results['env_file_exists'] = file_exists($envPath) ? 'yes ✓' : 'NO - FILE NOT FOUND!';

    // Show current MAIL_ENCRYPTION value from .env directly (bypassing config cache)
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        preg_match('/MAIL_ENCRYPTION=(.*)/', $envContent, $matches);
        $envValue = isset($matches[1]) ? trim($matches[1]) : 'NOT SET IN .ENV';
        $results['env_mail_encryption_raw'] = $envValue;
    }

    // Show what config() returns AFTER clearing
    $configValue = config('mail.mailers.smtp.encryption');
    $results['config_mail_encryption_after_clear'] = $configValue ?? 'NULL';

    return response()->json([
        'success' => true,
        'message' => 'All caches cleared successfully!',
        'diagnostics' => $results,
        'next_step' => 'Refresh the test-email page. If MAIL_ENCRYPTION is still null, OPcache might be disabled - contact hosting support.'
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

    // 2. Test SMTP connection (with timeout)
    try {
        // Set socket timeout to 5 seconds to prevent hanging
        $originalTimeout = ini_get('default_socket_timeout');
        ini_set('default_socket_timeout', 5);

        $encryption = config('mail.mailers.smtp.encryption');

        // For EsmtpTransport constructor:
        // - Port 587 with 'tls' → use false (STARTTLS)
        // - Port 465 with 'ssl' → use true (direct SSL)
        $useTls = ($encryption === 'ssl');

        $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
            config('mail.mailers.smtp.host'),
            config('mail.mailers.smtp.port'),
            $useTls
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
            'encryption_used' => $encryption,
        ];

        $transport->stop();

        // Restore original timeout
        ini_set('default_socket_timeout', $originalTimeout);
    } catch (\Exception $e) {
        // Restore original timeout
        if (isset($originalTimeout)) {
            ini_set('default_socket_timeout', $originalTimeout);
        }

        $results['connection_test'] = [
            'status' => 'FAILED',
            'error' => $e->getMessage(),
            'trace' => substr($e->getTraceAsString(), 0, 500) . '...', // Truncate trace
        ];
    }

    // 3. Try to send a test email
    if ($recipient) {
        try {
            // Check if encryption is missing for ports that require it
            $port = config('mail.mailers.smtp.port');
            $encryption = config('mail.mailers.smtp.encryption');

            if (in_array($port, [465, 587]) && empty($encryption)) {
                throw new \Exception("CRITICAL: Port {$port} requires MAIL_ENCRYPTION. Use 'ssl' for port 465 or 'tls' for port 587. Update .env and run 'php artisan config:clear'");
            }

            // Enable logging to capture actual SMTP errors
            $sentSuccessfully = false;
            $smtpError = null;

            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "This is a test email from BilligVentilation Dashboard.\n\nTimestamp: " . now()->toDateTimeString() . "\n\nIf you receive this, email delivery is working correctly!",
                    function($message) use ($recipient) {
                        $message->to($recipient)
                                ->subject('Test Email - BilligVentilation Dashboard - ' . now()->format('H:i:s'));
                    }
                );
                $sentSuccessfully = true;
            } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
                $smtpError = $e->getMessage();
                throw $e;
            }

            $results['email_test'] = [
                'status' => 'SUCCESS',
                'message' => "Test email sent successfully to {$recipient}",
                'recipient' => $recipient,
                'next_step' => 'Check your inbox (and spam folder)',
                'note' => 'Email sent immediately (not queued)',
                'queue_driver' => config('queue.default'),
                'troubleshooting' => [
                    'If email not received, check:',
                    '1. Spam/Junk folder',
                    '2. SPF/DKIM records for billigventilation.dk',
                    '3. Try sending to a different email provider',
                    '4. Check Laravel logs in storage/logs/laravel.log'
                ]
            ];
        } catch (\Exception $e) {
            $results['email_test'] = [
                'status' => 'FAILED',
                'error' => $e->getMessage(),
                'recipient' => $recipient,
                'trace' => substr($e->getTraceAsString(), 0, 500),
                'suggestion' => 'Check if your mail server requires authentication or has rate limiting'
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

// Temporary route to list all users (REMOVE AFTER USE)
Route::get('/list-users', function() {
    $users = \App\Models\User::all(['id', 'name', 'email', 'is_admin', 'created_at']);

    return response()->json([
        'total_users' => $users->count(),
        'users' => $users,
    ], 200, [], JSON_PRETTY_PRINT);
});

require __DIR__.'/auth.php';
