<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'due_date',
        'customer_number',
        'customer_name',
        'subject',
        'gross_amount',
        'remainder',
        'currency',
        'external_reference',
        'employee_number',
        'employee_name',
        'pdf_url',
        'raw_data',
        'last_synced_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'gross_amount' => 'decimal:2',
        'remainder' => 'decimal:2',
        'raw_data' => 'array',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Scope: Get overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('remainder', '>', 0)
                    ->where('due_date', '<', Carbon::today());
    }

    /**
     * Scope: Get unpaid invoices (includes not yet overdue)
     */
    public function scopeUnpaid($query)
    {
        return $query->where('remainder', '>', 0);
    }

    /**
     * Scope: Get paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('remainder', '=', 0);
    }

    /**
     * Scope: Filter by employee
     */
    public function scopeByEmployee($query, $employeeNumber)
    {
        return $query->where('employee_number', $employeeNumber);
    }

    /**
     * Scope: Unassigned invoices (no salesperson)
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('employee_number');
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeDateRange($query, $dateFrom = null, $dateTo = null)
    {
        if ($dateFrom) {
            $query->where('invoice_date', '>=', Carbon::parse($dateFrom)->startOfDay());
        }

        if ($dateTo) {
            $query->where('invoice_date', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        return $query;
    }

    /**
     * Scope: Search by text (customer name, invoice number, external reference)
     */
    public function scopeSearch($query, $searchTerm = null)
    {
        if (!$searchTerm) {
            return $query;
        }

        return $query->where(function($q) use ($searchTerm) {
            $q->where('customer_name', 'LIKE', "%{$searchTerm}%")
              ->orWhere('invoice_number', 'LIKE', "%{$searchTerm}%")
              ->orWhere('external_reference', 'LIKE', "%{$searchTerm}%")
              ->orWhere('subject', 'LIKE', "%{$searchTerm}%");
        });
    }

    /**
     * Accessor: Days overdue
     */
    protected function daysOverdue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->remainder <= 0) {
                    return 0;
                }

                if ($this->due_date >= Carbon::today()) {
                    return 0;
                }

                return Carbon::parse($this->due_date)->diffInDays(Carbon::today());
            }
        );
    }

    /**
     * Accessor: Days till due
     */
    protected function daysTillDue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->remainder <= 0) {
                    return 0;
                }

                if ($this->due_date < Carbon::today()) {
                    return 0;
                }

                return Carbon::today()->diffInDays(Carbon::parse($this->due_date));
            }
        );
    }

    /**
     * Accessor: Status
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->remainder <= 0) {
                    return 'paid';
                }

                if ($this->due_date < Carbon::today()) {
                    return 'overdue';
                }

                return 'unpaid';
            }
        );
    }

    /**
     * Create or update invoice from E-conomic API response
     */
    public static function createOrUpdateFromApi(array $apiData): self
    {
        return self::updateOrCreate(
            ['invoice_number' => $apiData['bookedInvoiceNumber']],
            [
                'invoice_date' => $apiData['date'] ?? null,
                'due_date' => $apiData['dueDate'] ?? null,
                'customer_number' => $apiData['customer']['customerNumber'] ?? null,
                'customer_name' => $apiData['recipient']['name'] ?? null,
                'subject' => $apiData['notes']['heading'] ?? null,
                'gross_amount' => $apiData['grossAmount'] ?? 0,
                'remainder' => $apiData['remainder'] ?? 0,
                'currency' => $apiData['currency'] ?? 'DKK',
                'external_reference' => $apiData['references']['other'] ?? null,
                'employee_number' => $apiData['references']['salesPerson']['employeeNumber'] ?? null,
                'employee_name' => $apiData['references']['salesPerson']['name'] ?? null,
                'pdf_url' => $apiData['pdf']['download'] ?? null,
                'raw_data' => $apiData,
                'last_synced_at' => now(),
            ]
        );
    }
}
