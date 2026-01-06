<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ExportEconomicData extends Command
{
    protected $signature = 'economic:export {--type=all : What to export (invoices, employees, all)}';
    protected $description = 'Export data from e-conomic API to JSON files for review';

    public function handle()
    {
        $type = $this->option('type');

        $this->info("Starting e-conomic data export...");

        $baseUrl = 'https://restapi.e-conomic.com';
        $headers = [
            'X-AppSecretToken' => config('e-conomic.app_secret_token'),
            'X-AgreementGrantToken' => config('e-conomic.agreement_grant_token'),
            'Content-Type' => 'application/json',
        ];

        if (in_array($type, ['invoices', 'all'])) {
            $this->exportInvoices($baseUrl, $headers);
        }

        if (in_array($type, ['employees', 'all'])) {
            $this->exportEmployees($baseUrl, $headers);
        }

        $this->info("✅ Export completed! Files saved to storage/app/economic-exports/");

        return 0;
    }

    protected function exportInvoices($baseUrl, $headers)
    {
        $this->info("Fetching invoices from e-conomic API...");

        $allInvoices = [];
        $pageNumber = 0;
        $pageSize = 1000;
        $hasMore = true;

        $bar = $this->output->createProgressBar();
        $bar->start();

        while ($hasMore) {
            $url = "{$baseUrl}/invoices/booked?pagesize={$pageSize}&skippages={$pageNumber}";

            $response = Http::timeout(60)->withHeaders($headers)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $invoices = $data['collection'] ?? [];

                $allInvoices = array_merge($allInvoices, $invoices);

                $bar->advance(count($invoices));

                $hasMore = count($invoices) === $pageSize;
                $pageNumber++;

                usleep(100000); // 100ms delay between requests
            } else {
                $this->error("API request failed: " . $response->status());
                $hasMore = false;
            }
        }

        $bar->finish();
        $this->newLine();

        // Save to file
        $filename = 'economic-exports/invoices_' . now()->format('Y-m-d_H-i-s') . '.json';
        Storage::put($filename, json_encode($allInvoices, JSON_PRETTY_PRINT));

        $this->info("✅ Exported " . count($allInvoices) . " invoices to: storage/app/{$filename}");

        // Also save a summary
        $summary = [
            'total_count' => count($allInvoices),
            'exported_at' => now()->toIso8601String(),
            'file' => $filename,
            'sample_invoice' => $allInvoices[0] ?? null,
        ];

        $summaryFile = 'economic-exports/invoices_summary_' . now()->format('Y-m-d_H-i-s') . '.json';
        Storage::put($summaryFile, json_encode($summary, JSON_PRETTY_PRINT));
    }

    protected function exportEmployees($baseUrl, $headers)
    {
        $this->info("Fetching employees from e-conomic API...");

        $url = "{$baseUrl}/employees";

        $response = Http::timeout(60)->withHeaders($headers)->get($url);

        if ($response->successful()) {
            $data = $response->json();
            $employees = $data['collection'] ?? [];

            // Save to file
            $filename = 'economic-exports/employees_' . now()->format('Y-m-d_H-i-s') . '.json';
            Storage::put($filename, json_encode($employees, JSON_PRETTY_PRINT));

            $this->info("✅ Exported " . count($employees) . " employees to: storage/app/{$filename}");

            // Also fetch detailed info for each employee
            $detailedEmployees = [];
            $bar = $this->output->createProgressBar(count($employees));
            $bar->start();

            foreach ($employees as $employee) {
                $employeeNumber = $employee['employeeNumber'];
                $detailUrl = "{$baseUrl}/employees/{$employeeNumber}";

                $detailResponse = Http::timeout(30)->withHeaders($headers)->get($detailUrl);

                if ($detailResponse->successful()) {
                    $detailedEmployees[] = $detailResponse->json();
                }

                $bar->advance();
                usleep(100000); // 100ms delay
            }

            $bar->finish();
            $this->newLine();

            // Save detailed employees
            $detailedFilename = 'economic-exports/employees_detailed_' . now()->format('Y-m-d_H-i-s') . '.json';
            Storage::put($detailedFilename, json_encode($detailedEmployees, JSON_PRETTY_PRINT));

            $this->info("✅ Saved detailed employee data to: storage/app/{$detailedFilename}");
        } else {
            $this->error("Failed to fetch employees: " . $response->status());
        }
    }
}
