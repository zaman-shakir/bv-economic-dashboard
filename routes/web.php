<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard for authenticated users
Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware('auth');

// Dashboard routes (authenticated users only)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/refresh', [DashboardController::class, 'refreshInvoices'])->name('dashboard.refresh');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    // Reminder routes
    Route::get('/reminders', [ReminderController::class, 'index'])->name('reminders.index');
    Route::post('/reminders/send', [ReminderController::class, 'sendReminder'])->name('reminders.send');
    Route::post('/reminders/send-employee', [ReminderController::class, 'sendEmployeeReminder'])->name('reminders.send-employee');
    Route::get('/reminders/{invoiceNumber}/history', [ReminderController::class, 'getReminderHistory'])->name('reminders.history');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// User management routes (admin only)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

// API routes
Route::prefix('api')->group(function () {
    Route::get('/overdue', [DashboardController::class, 'apiOverdue']);
});

// Language switching
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

require __DIR__.'/auth.php';
