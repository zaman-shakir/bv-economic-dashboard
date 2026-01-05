<?php
/**
 * Temporary Debug & Cache Clear Script
 * REMOVE THIS FILE AFTER DEBUGGING!
 *
 * Upload this to public/ folder via cPanel
 * Access at: https://dash.billigventilation.dk/debug.php
 */

// Prevent direct access from non-admin IPs (optional - remove if needed)
// $allowedIPs = ['YOUR_IP_HERE'];
// if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIPs)) {
//     die('Access denied');
// }

$action = $_GET['action'] ?? 'menu';
$basePath = dirname(__DIR__);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Laravel Debug Tools</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            margin: 0;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #4ec9b0; border-bottom: 2px solid #4ec9b0; padding-bottom: 10px; }
        h2 { color: #569cd6; }
        .menu { margin: 20px 0; }
        .menu a {
            display: inline-block;
            background: #0e639c;
            color: white;
            padding: 12px 24px;
            margin: 5px;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .menu a:hover { background: #1177bb; }
        .success {
            background: #1e5631;
            border-left: 4px solid #4ec9b0;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .error {
            background: #5a1d1d;
            border-left: 4px solid #f44747;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .info {
            background: #1f3a5f;
            border-left: 4px solid #569cd6;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        pre {
            background: #252526;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
            border: 1px solid #3e3e42;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #3e3e42;
        }
        th {
            background: #252526;
            color: #4ec9b0;
        }
        .warning { color: #ff8c00; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Laravel Debug Tools</h1>

        <div class="menu">
            <a href="?action=menu">🏠 Menu</a>
            <a href="?action=clear-cache">🗑️ Clear All Caches</a>
            <a href="?action=view-logs">📋 View Logs</a>
            <a href="?action=check-env">⚙️ Check Environment</a>
            <a href="?action=test-api">🔌 Test E-conomic API</a>
        </div>

        <?php

        switch ($action) {
            case 'clear-cache':
                echo "<h2>Clearing Caches...</h2>";

                $caches = [
                    'Config Cache' => $basePath . '/bootstrap/cache/config.php',
                    'Route Cache' => $basePath . '/bootstrap/cache/routes-v7.php',
                    'Services Cache' => $basePath . '/bootstrap/cache/services.php',
                    'Packages Cache' => $basePath . '/bootstrap/cache/packages.php',
                ];

                foreach ($caches as $name => $file) {
                    if (file_exists($file)) {
                        unlink($file);
                        echo "<div class='success'>✅ Cleared: $name</div>";
                    } else {
                        echo "<div class='info'>ℹ️ Not found (already clear): $name</div>";
                    }
                }

                // Clear storage cache files
                $cacheDir = $basePath . '/storage/framework/cache/data';
                if (is_dir($cacheDir)) {
                    $files = glob($cacheDir . '/*');
                    $count = 0;
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            unlink($file);
                            $count++;
                        }
                    }
                    echo "<div class='success'>✅ Cleared $count cache files from storage</div>";
                }

                echo "<div class='success'><strong>✅ All caches cleared successfully!</strong></div>";
                echo "<div class='info'>Now visit your dashboard to see if the issue is resolved.</div>";
                break;

            case 'view-logs':
                echo "<h2>📋 Laravel Logs (Last 200 Lines)</h2>";

                $logFile = $basePath . '/storage/logs/laravel.log';

                if (!file_exists($logFile)) {
                    echo "<div class='error'>❌ Log file not found at: $logFile</div>";
                } else {
                    $lines = file($logFile);
                    $lastLines = array_slice($lines, -200);
                    echo "<pre>" . htmlspecialchars(implode('', $lastLines)) . "</pre>";
                }
                break;

            case 'check-env':
                echo "<h2>⚙️ Environment Configuration</h2>";

                // Load .env file
                $envFile = $basePath . '/.env';
                if (!file_exists($envFile)) {
                    echo "<div class='error'>❌ .env file not found!</div>";
                } else {
                    echo "<div class='success'>✅ .env file exists</div>";

                    $envContent = file_get_contents($envFile);

                    // Check for e-conomic credentials
                    preg_match('/ECONOMIC_APP_SECRET_TOKEN=(.+)/', $envContent, $appToken);
                    preg_match('/ECONOMIC_AGREEMENT_GRANT_TOKEN=(.+)/', $envContent, $grantToken);

                    echo "<table>";
                    echo "<tr><th>Setting</th><th>Value</th></tr>";
                    echo "<tr><td>APP_ENV</td><td>" . getenv('APP_ENV') . "</td></tr>";
                    echo "<tr><td>APP_DEBUG</td><td>" . getenv('APP_DEBUG') . "</td></tr>";
                    echo "<tr><td>ECONOMIC_APP_SECRET_TOKEN</td><td>" .
                         (isset($appToken[1]) ? (trim($appToken[1]) === 'demo' ? '<span class="warning">⚠️ DEMO MODE</span>' : substr(trim($appToken[1]), 0, 15) . '...') : '❌ Not set') .
                         "</td></tr>";
                    echo "<tr><td>ECONOMIC_AGREEMENT_GRANT_TOKEN</td><td>" .
                         (isset($grantToken[1]) ? (trim($grantToken[1]) === 'demo' ? '<span class="warning">⚠️ DEMO MODE</span>' : substr(trim($grantToken[1]), 0, 15) . '...') : '❌ Not set') .
                         "</td></tr>";
                    echo "</table>";

                    if (isset($appToken[1]) && trim($appToken[1]) === 'demo') {
                        echo "<div class='warning'>⚠️ WARNING: You are in DEMO mode. Update your .env file with real API credentials.</div>";
                    }
                }
                break;

            case 'test-api':
                echo "<h2>🔌 Testing E-conomic API Connection</h2>";

                // Load .env file
                $envFile = $basePath . '/.env';
                $envContent = file_get_contents($envFile);

                preg_match('/ECONOMIC_APP_SECRET_TOKEN=(.+)/', $envContent, $appToken);
                preg_match('/ECONOMIC_AGREEMENT_GRANT_TOKEN=(.+)/', $envContent, $grantToken);

                $appTokenValue = isset($appToken[1]) ? trim($appToken[1]) : null;
                $grantTokenValue = isset($grantToken[1]) ? trim($grantToken[1]) : null;

                if (!$appTokenValue || !$grantTokenValue) {
                    echo "<div class='error'>❌ API tokens not configured in .env file</div>";
                } elseif ($appTokenValue === 'demo') {
                    echo "<div class='warning'>⚠️ Still in DEMO mode. Clear cache first!</div>";
                } else {
                    // Test API call
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://restapi.e-conomic.com/invoices/booked?pagesize=1');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'X-AppSecretToken: ' . $appTokenValue,
                        'X-AgreementGrantToken: ' . $grantTokenValue,
                        'Content-Type: application/json'
                    ]);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($httpCode === 200) {
                        echo "<div class='success'><strong>✅ API Connection Successful!</strong></div>";
                        echo "<h3>Response Preview:</h3>";
                        echo "<pre>" . htmlspecialchars(substr($response, 0, 1000)) . "...</pre>";
                    } else {
                        echo "<div class='error'><strong>❌ API Request Failed</strong></div>";
                        echo "<p>HTTP Status Code: $httpCode</p>";
                        echo "<h3>Response:</h3>";
                        echo "<pre>" . htmlspecialchars($response) . "</pre>";
                    }
                }
                break;

            default:
                echo "<h2>Welcome to Debug Tools</h2>";
                echo "<div class='info'>";
                echo "<p><strong>What would you like to do?</strong></p>";
                echo "<ul>";
                echo "<li><strong>Clear All Caches:</strong> Clears config, route, and application caches</li>";
                echo "<li><strong>View Logs:</strong> Shows the last 200 lines of Laravel logs</li>";
                echo "<li><strong>Check Environment:</strong> Displays .env configuration</li>";
                echo "<li><strong>Test E-conomic API:</strong> Tests connection to e-conomic API</li>";
                echo "</ul>";
                echo "</div>";

                echo "<div class='warning'>";
                echo "<p><strong>⚠️ SECURITY WARNING:</strong> This file provides direct access to your application. ";
                echo "DELETE this file immediately after debugging!</p>";
                echo "</div>";
                break;
        }

        ?>

        <hr style="border-color: #3e3e42; margin: 30px 0;">
        <p style="color: #858585; text-align: center;">
            ⚠️ Remember to delete this file after debugging: <code>public/debug.php</code>
        </p>
    </div>
</body>
</html>
