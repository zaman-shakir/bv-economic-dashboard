<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // E-conomic Invoice Fields
            $table->integer('invoice_number')->unique()->comment('bookedInvoiceNumber from API');
            $table->date('invoice_date')->comment('Invoice creation date');
            $table->date('due_date')->comment('Payment due date');

            // Customer Information
            $table->integer('customer_number')->comment('E-conomic customer.customerNumber');
            $table->string('customer_name', 255)->nullable()->comment('recipient.name from API');

            // Invoice Details
            $table->text('subject')->nullable()->comment('notes.heading from API');
            $table->decimal('gross_amount', 15, 2)->default(0)->comment('Total invoice amount');
            $table->decimal('remainder', 15, 2)->default(0)->comment('Outstanding amount to be paid');
            $table->string('currency', 10)->default('DKK')->comment('Invoice currency');
            $table->string('external_reference', 255)->nullable()->comment('references.other from API');

            // Employee/Salesperson
            $table->integer('employee_number')->nullable()->comment('references.salesPerson.employeeNumber');
            $table->string('employee_name', 255)->nullable()->comment('references.salesPerson.name');

            // Additional Data
            $table->text('pdf_url')->nullable()->comment('pdf.download URL from API');
            $table->json('raw_data')->nullable()->comment('Full API response for reference');

            // Metadata
            $table->timestamp('last_synced_at')->nullable()->comment('When this invoice was last updated from API');
            $table->timestamps();

            // Indexes for performance
            $table->index('invoice_number', 'idx_invoice_number');
            $table->index('employee_number', 'idx_employee_number');
            $table->index('customer_number', 'idx_customer_number');
            $table->index('due_date', 'idx_due_date');
            $table->index('remainder', 'idx_remainder');
            $table->index('invoice_date', 'idx_invoice_date');
            $table->index('last_synced_at', 'idx_last_synced');
            $table->index(['due_date', 'remainder'], 'idx_overdue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
