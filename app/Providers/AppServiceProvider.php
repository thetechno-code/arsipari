<?php

namespace App\Providers;

use App\Models\Archive;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\User;
use App\Policies\ArchivePolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\DocumentTypePolicy;
use App\Policies\UserPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Policies
        Gate::policy(Archive::class, ArchivePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(DocumentType::class, DocumentTypePolicy::class);

        // Set Carbon locale for Indonesian date formatting
        Carbon::setLocale('id');
    }
}
