<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\ArchiveVersionController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RetentionPolicyController;
use App\Http\Controllers\SystemHealthController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────
// Guest routes
// ─────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// ─────────────────────────────────────────────
// Authenticated routes
// ─────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Self Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');

    // ── Archive Main Routes ──
    Route::get('/archives', [ArchiveController::class, 'index'])->name('archives.index');
    Route::get('/archives/create', [ArchiveController::class, 'create'])->name('archives.create');
    Route::post('/archives', [ArchiveController::class, 'store'])->name('archives.store');
    Route::get('/archives/{archive}', [ArchiveController::class, 'show'])->name('archives.show');
    Route::get('/archives/{archive}/edit', [ArchiveController::class, 'edit'])->name('archives.edit');
    Route::put('/archives/{archive}', [ArchiveController::class, 'update'])->name('archives.update');
    Route::get('/archives/{archive}/download', [ArchiveController::class, 'download'])->name('archives.download');

    // ── Archive Versioning Routes ──
    Route::post('/archives/{archive}/versions', [ArchiveVersionController::class, 'store'])->name('archives.versions.store');
    Route::get('/archives/{archive}/versions/{version}/download', [ArchiveVersionController::class, 'download'])->name('archives.versions.download');

    // ── Archive Reporting Routes ──
    Route::get('/reports/archives', [ReportController::class, 'index'])->name('reports.archives');
    Route::get('/reports/archives/export/excel', [ReportController::class, 'exportExcel'])->name('reports.archives.excel');
    Route::get('/reports/archives/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.archives.pdf');

    // ── Admin Only Routes ──
    Route::middleware('role:admin')->group(function () {
        // Backup Management
        Route::get('/admin/backups', [BackupController::class, 'index'])->name('admin.backups.index');
        Route::post('/admin/backups', [BackupController::class, 'store'])->name('admin.backups.store');
        Route::get('/admin/backups/{filename}/download', [BackupController::class, 'download'])->name('admin.backups.download');
        Route::delete('/admin/backups/{filename}', [BackupController::class, 'destroy'])->name('admin.backups.destroy');

        // System Health & Info
        Route::get('/admin/system', [SystemHealthController::class, 'index'])->name('admin.system.index');

        // Trash & Restoration
        Route::get('/trash', [TrashController::class, 'index'])->name('archives.trash');
        Route::delete('/archives/{archive}', [ArchiveController::class, 'destroy'])->name('archives.destroy');
        Route::put('/archives/{id}/restore', [ArchiveController::class, 'restore'])->name('archives.restore');
        Route::put('/archives/{archive}/status', [ArchiveController::class, 'toggleStatus'])->name('archives.status');
        Route::put('/archives/{archive}/versions/{version}/restore', [ArchiveVersionController::class, 'restore'])->name('archives.versions.restore');

        // Audit Trail UI
        Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        // Users Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::put('/users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.status');
        Route::put('/users/{user}/password', [UserController::class, 'resetPassword'])->name('users.password');

        // Categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::put('/categories/{category}/status', [CategoryController::class, 'toggleStatus'])->name('categories.status');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Departments / Units
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::put('/departments/{department}/status', [DepartmentController::class, 'toggleStatus'])->name('departments.status');
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        // Document Types
        Route::get('/document-types', [DocumentTypeController::class, 'index'])->name('document-types.index');
        Route::post('/document-types', [DocumentTypeController::class, 'store'])->name('document-types.store');
        Route::put('/document-types/{documentType}', [DocumentTypeController::class, 'update'])->name('document-types.update');
        Route::put('/document-types/{documentType}/status', [DocumentTypeController::class, 'toggleStatus'])->name('document-types.status');
        Route::delete('/document-types/{documentType}', [DocumentTypeController::class, 'destroy'])->name('document-types.destroy');

        // Retention Policies Master Data
        Route::get('/retention-policies', [RetentionPolicyController::class, 'index'])->name('retention-policies.index');
        Route::post('/retention-policies', [RetentionPolicyController::class, 'store'])->name('retention-policies.store');
        Route::put('/retention-policies/{retentionPolicy}', [RetentionPolicyController::class, 'update'])->name('retention-policies.update');
        Route::put('/retention-policies/{retentionPolicy}/status', [RetentionPolicyController::class, 'toggleStatus'])->name('retention-policies.status');
        Route::delete('/retention-policies/{retentionPolicy}', [RetentionPolicyController::class, 'destroy'])->name('retention-policies.destroy');
    });
});
