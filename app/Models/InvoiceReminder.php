<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceReminder extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_email',
        'customer_name',
        'amount_due',
        'sent_by',
        'email_sent',
        'email_error',
    ];

    protected $casts = [
        'email_sent' => 'boolean',
        'amount_due' => 'decimal:2',
    ];

    /**
     * Get the user who sent the reminder
     */
    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
