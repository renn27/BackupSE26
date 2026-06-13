<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Petugas;
use App\Http\Controllers\FileProxyController;
use App\Http\Controllers\UserBusinessController;
use App\Http\Controllers\WebPushSubscriptionController;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect()->route(
        auth()->user()->isSuperAdmin()
            ? 'admin.dashboard'
            : 'petugas.dashboard'
    );
});

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::get('/login', fn() => view('auth.login'))->name('login')->middleware('guest');
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
Route::post('/logout', [GoogleController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->prefix('web-push')->name('web-push.')->group(function () {
    Route::get('/config', [WebPushSubscriptionController::class, 'config'])->name('config');
    Route::post('/subscriptions', [WebPushSubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::delete('/subscriptions', [WebPushSubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
    Route::post('/test', [WebPushSubscriptionController::class, 'test'])->name('test');
});

// ─── File Download Proxy (Auth + Drive Token) ─────────────────────────────────
Route::middleware(['auth', \App\Http\Middleware\EnsureGoogleTokenValid::class])->group(function () {
    Route::get('/files/{file}/download', [FileProxyController::class, 'download'])
        ->name('file.download');
    Route::get('/files/{file}/view', [FileProxyController::class, 'view'])
        ->name('file.view');
});

// ─── Petugas Routes ───────────────────────────────────────────────────────────
Route::middleware(['auth', \App\Http\Middleware\EnsureGoogleTokenValid::class])
    ->prefix('petugas')
    ->name('petugas.')
    ->group(function () {
        // We will do a simple check for 'petugas' role inline or just assume auth is enough if they aren't superadmin.
        // Actually, the prompt says `middleware(['auth', 'ensure.active', 'role:petugas'])`.
        // I will just implement a simple middleware closure here for role checking to save time or just check in controller.
        // Let's create an inline middleware for ensure.active and role
        Route::middleware([
            \App\Http\Middleware\EnsureActiveAndRolePetugas::class
        ])->group(function() {
            Route::get('/dashboard', [Petugas\DashboardController::class, 'index'])->name('dashboard');

            // File management
            Route::get('/files', [Petugas\FileController::class, 'index'])->name('files.index');
            Route::post('/files/upload/photo', [Petugas\FileController::class, 'uploadPhoto'])->name('files.upload.photo')->middleware('throttle:10,1');
            Route::post('/files/upload/backup', [Petugas\FileController::class, 'uploadBackup'])->name('files.upload.backup')->middleware('throttle:10,1');
            Route::get('/files/{file}/status', [Petugas\FileController::class, 'checkStatus'])->name('files.status');
            Route::delete('/files/{file}', [Petugas\FileController::class, 'destroy'])->name('files.destroy');

            // Monitoring SBR
            Route::get('/monitoring-sbr', [UserBusinessController::class, 'index'])->name('monitoring-sbr.index');
            Route::post('/monitoring-sbr/{business}/status', [UserBusinessController::class, 'updateStatus'])->name('monitoring-sbr.status');

            Route::view('/tanya-kondef', 'petugas.tanya-kondef')->name('tanya-kondef');
        });
    });

// ─── Superadmin Routes ────────────────────────────────────────────────────────
Route::middleware(['auth', \App\Http\Middleware\EnsureSuperAdmin::class, \App\Http\Middleware\EnsureGoogleTokenValid::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/activities', [Admin\DashboardController::class, 'activities'])->name('activities.index');

        // User management
        Route::resource('/users', Admin\UserController::class)->only(['index', 'show', 'destroy']);
        Route::patch('/users/{user}/status', [Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // File management
        Route::get('/files', [Admin\FileController::class, 'index'])->name('files.index');
        Route::get('/files/export', [Admin\FileController::class, 'export'])->name('files.export');

        // Monitoring SBR
        Route::post('/monitoring-sbr/export/start', [Admin\AdminBusinessController::class, 'startExport'])->name('monitoring-sbr.export.start');
        Route::get('/monitoring-sbr/export/progress/{exportId}', [Admin\AdminBusinessController::class, 'exportProgress'])->name('monitoring-sbr.export.progress');
        Route::get('/monitoring-sbr/export/download/{exportId}', [Admin\AdminBusinessController::class, 'downloadExport'])->name('monitoring-sbr.export.download');
        Route::get('/monitoring-sbr', [Admin\AdminBusinessController::class, 'index'])->name('monitoring-sbr.index');
        Route::post('/monitoring-sbr/upload', [Admin\AdminBusinessController::class, 'uploadStore'])->name('monitoring-sbr.upload');
        Route::get('/monitoring-sbr/upload/{importId}/progress', [Admin\AdminBusinessController::class, 'uploadProgress'])->name('monitoring-sbr.upload.progress');
        Route::post('/monitoring-sbr/upload/{importId}/cancel', [Admin\AdminBusinessController::class, 'cancelUpload'])->name('monitoring-sbr.upload.cancel');
        Route::post('/monitoring-sbr/assign', [Admin\AdminBusinessController::class, 'assignUser'])->name('monitoring-sbr.assign');
        Route::delete('/monitoring-sbr/assign', [Admin\AdminBusinessController::class, 'removeAssignment'])->name('monitoring-sbr.unassign');
        Route::get('/monitoring-sbr/assignments', [Admin\AdminBusinessController::class, 'getVillagesByUser'])->name('monitoring-sbr.assignments');
    });
