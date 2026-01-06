<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;

class ExportFullInvoices extends Command
{
    protected $signature = 'invoices:export-full';
    protected $description = 'Export all invoices from database to JSON file';

    public function handle()
    {
        $this->info('Starting full invoice export...');

        $totalCount = Invoice::count();
        $this->info("Total invoices to export: " . number_format($totalCount));

        $exportPath = storage_path('exports/all_invoices_full.json');

        // Ensure directory exists
        if (!is_dir(storage_path('exports'))) {
            mkdir(storage_path('exports'), 0755, true);
        }

        // Open file for writing
        $file = fopen($exportPath, 'w');

        // Write opening
        fwrite($file, "{\n");
        fwrite($file, "  \"exported_at\": \"" . now()->toIso8601String() . "\",\n");
        fwrite($file, "  \"total_count\": " . $totalCount . ",\n");
        fwrite($file, "  \"employees_in_system\": 10,\n");
        fwrite($file, "  \"employees_with_invoices\": 6,\n");
        fwrite($file, "  \"employee_numbers_with_invoices\": [1, 3, 4, 5, 6, 7],\n");
        fwrite($file, "  \"invoices\": [\n");

        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        $first = true;

        // Process in chunks to avoid memory issues
        Invoice::chunk(1000, function ($invoices) use ($file, &$first, $bar) {
            foreach ($invoices as $inv) {
                if (!$first) {
                    fwrite($file, ",\n");
                }
                $first = false;

                $data = [
                    'invoice_number' => $inv->invoice_number,
                    'invoice_date' => $inv->invoice_date?->format('Y-m-d'),
                    'due_date' => $inv->due_date?->format('Y-m-d'),
                    'customer_number' => $inv->customer_number,
                    'customer_name' => $inv->customer_name,
                    'subject' => $inv->subject,
                    'gross_amount' => (float) $inv->gross_amount,
                    'remainder' => (float) $inv->remainder,
                    'currency' => $inv->currency,
                    'external_reference' => $inv->external_reference,
                    'employee_number' => $inv->employee_number,
                    'employee_name' => $inv->employee_name,
                    'pdf_url' => $inv->pdf_url,
                    'status' => $inv->status,
                    'days_overdue' => $inv->days_overdue,
                    'raw_data' => $inv->raw_data,
                ];

                fwrite($file, '    ' . json_encode($data, JSON_UNESCAPED_SLASHES));
                $bar->advance();
            }
        });

        // Write closing
        fwrite($file, "\n  ]\n");
        fwrite($file, "}\n");
        fclose($file);

        $bar->finish();
        $this->newLine();

        $fileSize = filesize($exportPath);
        $this->info("✅ Exported " . number_format($totalCount) . " invoices");
        $this->info("📁 File: storage/exports/all_invoices_full.json");
        $this->info("📊 Size: " . round($fileSize / 1024 / 1024, 2) . " MB");

        return 0;
    }
}
