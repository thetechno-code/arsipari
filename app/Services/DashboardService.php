<?php

namespace App\Services;

use App\Models\Archive;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get KPI summary metrics for the dashboard.
     */
    public function getSummary(): array
    {
        $currentYear = (int) date('Y');

        return [
            'total_archives'        => Archive::withoutTrashed()->count(),
            'current_year_archives' => Archive::withoutTrashed()->where('year', $currentYear)->count(),
            'total_categories'      => Category::active()->roots()->count(),
            'total_departments'     => Department::active()->count(),
            'current_year'          => $currentYear,
        ];
    }

    /**
     * Get recent archives for dashboard discovery.
     */
    public function getRecentArchives(int $limit = 10): Collection
    {
        return Archive::with(['category.parent', 'department', 'uploader'])
            ->withoutTrashed()
            ->latest('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get top categories grouped by archive count.
     */
    public function getCategoryStatistics(int $limit = 5): BaseCollection
    {
        return Category::select('categories.id', 'categories.name', 'categories.code', DB::raw('count(archives.id) as archive_count'))
            ->join('archives', 'categories.id', '=', 'archives.category_id')
            ->whereNull('archives.deleted_at')
            ->groupBy('categories.id', 'categories.name', 'categories.code')
            ->orderByDesc('archive_count')
            ->take($limit)
            ->get();
    }

    /**
     * Get archive count statistics per year.
     */
    public function getYearStatistics(int $limit = 5): BaseCollection
    {
        return Archive::select('year', DB::raw('count(*) as count'))
            ->whereNull('deleted_at')
            ->groupBy('year')
            ->orderByDesc('year')
            ->take($limit)
            ->get();
    }
}
