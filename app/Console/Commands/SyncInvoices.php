<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EconomicInvoiceService;

class SyncInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:sync
                            {--force : Force sync even if recently synced}
                            {--test-limit= : Limit number of invoices to sync for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all invoices from E-conomic API to local database';

    /**
     * Execute the console command.
     */
    public function handle(EconomicInvoiceService $service)
    {
        if (!$this->option('quiet')) {
            $this->info('🚀 Starting invoice sync from E-conomic...');
            $this->newLine();
        }

        // Check last sync time
        $lastSync = $service->getLastSyncTime();

        if ($lastSync && $lastSync->diffInMinutes(now()) < 30 && !$this->option('force')) {
            $this->warn("⚠️  Last sync was {$lastSync->diffForHumans()}. Use --force to sync again.");
            return Command::FAILURE;
        }

        // Show progress bar
        if (!$this->option('quiet')) {
            $testLimit = $this->option('test-limit');
            if ($testLimit) {
                $this->info("Fetching invoices in chunks of 1000 (test limit: {$testLimit})...");
            } else {
                $this->info('Fetching invoices in chunks of 1000...');
            }
        }

        // Start sync
        $startTime = microtime(true);
        $stats = $service->syncAllInvoices($this->option('test-limit') ? (int)$this->option('test-limit') : null);
        $duration = microtime(true) - $startTime;

        if (!$this->option('quiet')) {
            $this->newLine();
            $this->info('✅ Sync completed successfully!');
            $this->newLine();

            // Display statistics table
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Pages Processed', $stats['total_pages']],
                    ['Invoices Fetched', number_format($stats['total_fetched'])],
                    ['New Invoices', number_format($stats['total_created'])],
                    ['Updated Invoices', number_format($stats['total_updated'])],
                    ['Duration', round($duration, 2) . ' seconds'],
                    ['Errors', count($stats['errors'])],
                ]
            );

            // Show errors if any
            if (!empty($stats['errors'])) {
                $this->newLine();
                $this->error('❌ Errors occurred during sync:');
                foreach (array_slice($stats['errors'], 0, 10) as $error) {
                    $this->line('  • ' . $error);
                }
                if (count($stats['errors']) > 10) {
                    $this->line('  ... and ' . (count($stats['errors']) - 10) . ' more errors');
                }
            }

            // Show database stats
            $dbStats = $service->getSyncStats();
            $this->newLine();
            $this->info('📊 Database Statistics:');
            $this->table(
                ['Category', 'Count'],
                [
                    ['Total Invoices', number_format($dbStats['total_invoices'])],
                    ['Overdue', number_format($dbStats['overdue_count'])],
                    ['Unpaid (not overdue)', number_format($dbStats['unpaid_count'])],
                    ['Paid', number_format($dbStats['paid_count'])],
                    ['Unassigned (no salesperson)', number_format($dbStats['unassigned_count'])],
                ]
            );
        }

        return Command::SUCCESS;
    }
}
