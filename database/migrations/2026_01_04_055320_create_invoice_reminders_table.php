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
        Schema::create('invoice_reminders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number');
            $table->string('customer_email');
            $table->string('customer_name');
            $table->decimal('amount_due', 10, 2);
            $table->foreignId('sent_by')->constrained('users')->onDelete('cascade');
            $table->boolean('email_sent')->default(false);
            $table->text('email_error')->nullable();
            $table->timestamps();

            // Index for faster lookups
            $table->index(['invoice_number', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_reminders');
    }
};
