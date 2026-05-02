<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\MoodController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\FocusSessionController;
use App\Http\Controllers\SecretLetterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SpecialDateController;
use App\Http\Controllers\AnalyticsController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Tasks
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');

    // Goals
    Route::resource('goals', GoalController::class)->except(['edit', 'show']);

    // Mood
    Route::post('/mood', [MoodController::class, 'store'])->name('mood.store');
    Route::get('/mood/history', [MoodController::class, 'history'])->name('mood.history');

    // Partner
    Route::post('/partner/connect', [PartnerController::class, 'connect'])->name('partner.connect');
    Route::delete('/partner/disconnect', [PartnerController::class, 'disconnect'])->name('partner.disconnect');

    // Focus Sessions
    Route::get('/focus', [FocusSessionController::class, 'index'])->name('focus.index');
    Route::post('/focus/start', [FocusSessionController::class, 'start'])->name('focus.start');
    Route::post('/focus/stop', [FocusSessionController::class, 'stop'])->name('focus.stop');
    Route::get('/focus/status', [FocusSessionController::class, 'status'])->name('focus.status');

    // Secret Letters
    Route::get('/letters', [SecretLetterController::class, 'index'])->name('letters.index');
    Route::get('/letters/create', [SecretLetterController::class, 'create'])->name('letters.create');
    Route::post('/letters', [SecretLetterController::class, 'store'])->name('letters.store');
    Route::get('/letters/{letter}', [SecretLetterController::class, 'show'])->name('letters.show');
    Route::delete('/letters/{letter}', [SecretLetterController::class, 'destroy'])->name('letters.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifications/unread', [NotificationController::class, 'getUnread'])->name('notifications.unread');

    // Special Dates
    Route::get('/dates', [SpecialDateController::class, 'index'])->name('dates.index');
    Route::post('/dates', [SpecialDateController::class, 'store'])->name('dates.store');
    Route::delete('/dates/{specialDate}', [SpecialDateController::class, 'destroy'])->name('dates.destroy');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Profile (Breeze)
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';