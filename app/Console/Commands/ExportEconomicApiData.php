<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ExportEconomicApiData extends Command
{
    protected $signature = 'economic:export-api';
    protected $description = 'Export raw data directly from e-conomic API to JSON file';

    public function handle()
    {
        $this->info('Starting e-conomic API export...');

        $baseUrl = 'https://restapi.e-conomic.com';
        $headers = [
            'X-AppSecretToken' => config('e-conomic.app_secret_token'),
            'X-AgreementGrantToken' => config('e-conomic.agreement_grant_token'),
            'Content-Type' => 'application/json',
        ];

        $exportPath = storage_path('exports/economic_api_raw_data.json');

        // Ensure directory exists
        if (!is_dir(storage_path('exports'))) {
            mkdir(storage_path('exports'), 0755, true);
        }

        // Open file for writing
        $file = fopen($exportPath, 'w');

        // Write opening
        fwrite($file, "{\n");
        fwrite($file, "  \"exported_at\": \"" . now()->toIso8601String() . "\",\n");
        fwrite($file, "  \"source\": \"e-conomic REST API\",\n");

        // Export Employees
        $this->info('Fetching employees from API...');
        fwrite($file, "  \"employees\": ");

        $employeesResponse = Http::timeout(60)->withHeaders($headers)->get("{$baseUrl}/employees");
        if ($employeesResponse->successful()) {
            $employeesData = $employeesResponse->json();
            fwrite($file, json_encode($employeesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info("✅ Exported " . count($employeesData['collection'] ?? []) . " employees");
        } else {
            fwrite($file, "null");
            $this->error("Failed to fetch employees");
        }

        fwrite($file, ",\n");

        // Export Invoices
        $this->info('Fetching invoices from API...');
        fwrite($file, "  \"invoices\": {\n");
        fwrite($file, "    \"pagination\": [],\n");
        fwrite($file, "    \"collection\": [\n");

        $allInvoices = [];
        $pageNumber = 0;
        $pageSize = 1000;
        $hasMore = true;

        $bar = $this->output->createProgressBar();
        $bar->start();

        $first = true;

        while ($hasMore) {
            $url = "{$baseUrl}/invoices/booked?pagesize={$pageSize}&skippages={$pageNumber}";
            $response = Http::timeout(60)->withHeaders($headers)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $invoices = $data['collection'] ?? [];

                foreach ($invoices as $invoice) {
                    if (!$first) {
                        fwrite($file, ",\n");
                    }
                    $first = false;
                    fwrite($file, '      ' . json_encode($invoice, JSON_UNESCAPED_SLASHES));
                    $bar->advance();
                }

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

        // Write closing
        fwrite($file, "\n    ],\n");
        fwrite($file, "    \"total_count\": " . ($pageNumber * $pageSize - ($pageSize - count($invoices ?? []))) . "\n");
        fwrite($file, "  }\n");
        fwrite($file, "}\n");
        fclose($file);

        $fileSize = filesize($exportPath);
        $this->info("✅ Export completed");
        $this->info("📁 File: storage/exports/economic_api_raw_data.json");
        $this->info("📊 Size: " . round($fileSize / 1024 / 1024, 2) . " MB");

        return 0;
    }
}
