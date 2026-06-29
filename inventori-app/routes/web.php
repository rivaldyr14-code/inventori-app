<?php

use App\Http\Controllers\Api\CategorySearchController;
use App\Http\Controllers\Api\ProductSearchController;
use App\Http\Controllers\Api\RoleSearchController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ExportImportLogController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PendingRegistrationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', LandingController::class)->name('landing');

/*
|--------------------------------------------------------------------------
| Authentication routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->name('login.store');

Route::get('/register', [RegisteredUserController::class, 'create'])
    ->middleware('guest')
    ->name('register');

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->name('register.store');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // File attachment download
    Route::get('/attachments/{entity}/{id}', [AttachmentController::class, 'download'])
        ->name('attachments.download');

    // Category management
    Route::resource('categories', CategoryController::class);

    // Product management
    Route::resource('products', ProductController::class);

    // Stock Transaction management
    Route::resource('stock-transactions', StockTransactionController::class);

    // Role management - view (any role)
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');

    // Role management - CRUD (admin only)
    Route::get('/roles/create', [RoleController::class, 'create'])
        ->middleware('role:Administrator')
        ->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('role:Administrator')
        ->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
        ->middleware('role:Administrator')
        ->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware('role:Administrator')
        ->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('role:Administrator')
        ->name('roles.destroy');

    // User management (admin only)
    Route::resource('users', UserController::class)->middleware('role:Administrator');

    // Pending registrations (admin only)
    Route::get('/admin/pending-registrations', [PendingRegistrationController::class, 'index'])
        ->middleware('role:Administrator')
        ->name('admin.pending-registrations');
    Route::post('/admin/pending-registrations/{id}/approve', [PendingRegistrationController::class, 'approve'])
        ->middleware('role:Administrator')
        ->name('admin.pending-registrations.approve');
    Route::post('/admin/pending-registrations/{id}/reject', [PendingRegistrationController::class, 'reject'])
        ->middleware('role:Administrator')
        ->name('admin.pending-registrations.reject');

    // Export/Import
    Route::post('/export/{entity}', [ExportController::class, 'dispatch'])->name('export.dispatch');
    Route::get('/exports/{log}/download', [ExportController::class, 'download'])->name('export.download');
    Route::post('/import/{entity}', [ImportController::class, 'dispatch'])->name('import.dispatch');
    Route::post('/import/upload', [ImportController::class, 'upload'])->name('import.upload');
    Route::post('/import/{entity}/preview', [ImportController::class, 'preview'])->name('import.preview');

    // Autocomplete API endpoints
    Route::get('/api/categories/search', CategorySearchController::class)->name('api.categories.search');
    Route::get('/api/products/search', ProductSearchController::class)->name('api.products.search');
    Route::get('/api/roles/search', RoleSearchController::class)->name('api.roles.search');

    // Export/Import Logs
    Route::get('/export-import-logs', [ExportImportLogController::class, 'index'])->name('export-import-logs.index');
});
