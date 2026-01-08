#!/usr/bin/env php
<?php
/**
 * Redis Availability Checker
 * Tests if Redis is installed and accessible on the server
 */

echo "=== Redis Availability Check ===\n\n";

// Check 1: Redis extension installed in PHP
echo "1. Checking PHP Redis extension...\n";
if (extension_loaded('redis')) {
    echo "   ✅ PHP Redis extension is INSTALLED\n";
    $phpRedisVersion = phpversion('redis');
    echo "   Version: {$phpRedisVersion}\n";
} else {
    echo "   ❌ PHP Redis extension is NOT installed\n";
}

echo "\n";

// Check 2: Predis package (PHP Redis client)
echo "2. Checking Predis package (PHP client)...\n";
if (class_exists('Predis\Client')) {
    echo "   ✅ Predis package is INSTALLED\n";
} else {
    echo "   ❌ Predis package is NOT installed\n";
    echo "   Note: Can install with: composer require predis/predis\n";
}

echo "\n";

// Check 3: Redis server running
echo "3. Checking if Redis server is running...\n";
$redisHosts = [
    '127.0.0.1:6379' => 'localhost:6379 (default)',
    '127.0.0.1:6380' => 'localhost:6380 (alternate)',
    'localhost:6379' => 'localhost:6379 (hostname)',
];

$foundRedis = false;

foreach ($redisHosts as $host => $label) {
    list($hostname, $port) = explode(':', $host);

    echo "   Trying {$label}...\n";

    // Try socket connection
    $socket = @fsockopen($hostname, (int)$port, $errno, $errstr, 1);
    if ($socket) {
        echo "   ✅ Redis server is RUNNING on {$label}\n";
        fclose($socket);
        $foundRedis = true;

        // Try to connect with Redis extension
        if (extension_loaded('redis')) {
            try {
                $redis = new Redis();
                if ($redis->connect($hostname, (int)$port, 1)) {
                    echo "   ✅ Successfully connected via PHP Redis extension\n";

                    // Test PING command
                    $pong = $redis->ping();
                    echo "   ✅ PING test: " . ($pong ? 'Success' : 'Failed') . "\n";

                    // Get Redis info
                    $info = $redis->info();
                    if (isset($info['redis_version'])) {
                        echo "   Redis version: {$info['redis_version']}\n";
                    }

                    $redis->close();
                }
            } catch (Exception $e) {
                echo "   ⚠️  Connection attempt failed: {$e->getMessage()}\n";
            }
        }

        break; // Found working Redis, no need to check others
    }
}

if (!$foundRedis) {
    echo "   ❌ Redis server is NOT running on any checked ports\n";
}

echo "\n";

// Check 4: Laravel config
echo "4. Checking Laravel Redis configuration...\n";
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);

    // Check CACHE_DRIVER
    if (preg_match('/CACHE_DRIVER=(\w+)/', $envContent, $matches)) {
        $cacheDriver = $matches[1];
        echo "   Current CACHE_DRIVER: {$cacheDriver}\n";

        if ($cacheDriver === 'redis') {
            echo "   ✅ Already configured to use Redis\n";
        } else {
            echo "   ℹ️  Currently using: {$cacheDriver}\n";
        }
    }

    // Check REDIS_HOST
    if (preg_match('/REDIS_HOST=(.+)/', $envContent, $matches)) {
        $redisHost = trim($matches[1]);
        echo "   REDIS_HOST: {$redisHost}\n";
    }

    // Check REDIS_PORT
    if (preg_match('/REDIS_PORT=(\d+)/', $envContent, $matches)) {
        $redisPort = $matches[1];
        echo "   REDIS_PORT: {$redisPort}\n";
    }
} else {
    echo "   ⚠️  .env file not found\n";
}

echo "\n";

// Final recommendation
echo "=== Summary & Recommendations ===\n\n";

if (extension_loaded('redis') && $foundRedis) {
    echo "✅ REDIS IS AVAILABLE!\n\n";
    echo "You can switch to Redis caching:\n";
    echo "1. Update .env file:\n";
    echo "   CACHE_DRIVER=redis\n";
    echo "   REDIS_HOST=127.0.0.1\n";
    echo "   REDIS_PORT=6379\n\n";
    echo "2. Clear and rebuild cache:\n";
    echo "   php artisan config:clear\n";
    echo "   php artisan cache:clear\n";
    echo "   php artisan config:cache\n\n";
    echo "3. Test your dashboard\n";
} elseif (!extension_loaded('redis') && $foundRedis) {
    echo "⚠️  REDIS SERVER IS RUNNING, but PHP extension not installed\n\n";
    echo "To use Redis, install PHP Redis extension:\n";
    echo "- On cPanel: Contact hosting provider or use EasyApache/MultiPHP\n";
    echo "- Or install Predis: composer require predis/predis\n";
} elseif (extension_loaded('redis') && !$foundRedis) {
    echo "⚠️  PHP REDIS EXTENSION IS INSTALLED, but server not running\n\n";
    echo "To use Redis, ask your hosting provider to:\n";
    echo "- Install and start Redis server\n";
    echo "- Or enable Redis in cPanel (if available)\n";
} else {
    echo "❌ REDIS IS NOT AVAILABLE\n\n";
    echo "Your current file cache is working fine!\n";
    echo "If you want Redis, contact your hosting provider:\n";
    echo "- Ask to install Redis server\n";
    echo "- Ask to enable PHP Redis extension\n";
}

echo "\n";
