<?php

namespace App\Http\Controllers;

use App\Enums\AuditAction;
use App\Models\Category;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\RetentionPolicy;
use App\Services\AuditLogService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Exception;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Display the Archive Report page with filter form, summary counters, and preview table.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'date_from'           => ['nullable', 'date'],
            'date_to'             => ['nullable', 'date', 'after_or_equal:date_from'],
            'category_id'         => ['nullable', 'integer', 'exists:categories,id'],
            'department_id'       => ['nullable', 'integer', 'exists:departments,id'],
            'document_type'       => ['nullable', 'string'],
            'status'              => ['nullable', 'string', 'in:active,inactive'],
            'retention_policy_id' => ['nullable', 'integer', 'exists:retention_policies,id'],
            'retention_status'    => ['nullable', 'string', 'in:permanent,expired,due_soon,not_due'],
            'year'                => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'sort'                => ['nullable', 'string', 'in:created_at,document_date,year,title'],
            'direction'           => ['nullable', 'string', 'in:asc,desc'],
        ]);

        $summary  = $this->reportService->getReportSummary($validated);
        $archives = $this->reportService->getPaginatedReport($validated, 20);

        $categories        = Category::whereNull('parent_id')->with('children')->get();
        $departments       = Department::where('is_active', true)->orderBy('name')->get();
        $documentTypes     = DocumentType::where('is_active', true)->orderBy('name')->get();
        $retentionPolicies = RetentionPolicy::where('is_active', true)->orderBy('name')->get();

        return view('reports.index', compact(
            'archives',
            'summary',
            'categories',
            'departments',
            'documentTypes',
            'retentionPolicies',
            'validated'
        ));
    }

    /**
     * Export report to Excel.
     */
    public function exportExcel(Request $request)
    {
        $validated = $request->validate([
            'date_from'           => ['nullable', 'date'],
            'date_to'             => ['nullable', 'date', 'after_or_equal:date_from'],
            'category_id'         => ['nullable', 'integer', 'exists:categories,id'],
            'department_id'       => ['nullable', 'integer', 'exists:departments,id'],
            'document_type'       => ['nullable', 'string'],
            'status'              => ['nullable', 'string', 'in:active,inactive'],
            'retention_policy_id' => ['nullable', 'integer', 'exists:retention_policies,id'],
            'retention_status'    => ['nullable', 'string'],
            'year'                => ['nullable', 'integer'],
        ]);

        $this->auditLogService->record(
            AuditAction::REPORT_EXPORTED_EXCEL,
            "Mengunduh Laporan Arsip versi Excel",
            null,
            ['filters' => array_filter($validated)]
        );

        return $this->reportService->exportExcel($validated);
    }

    /**
     * Export report to PDF.
     */
    public function exportPdf(Request $request)
    {
        $validated = $request->validate([
            'date_from'           => ['nullable', 'date'],
            'date_to'             => ['nullable', 'date', 'after_or_equal:date_from'],
            'category_id'         => ['nullable', 'integer', 'exists:categories,id'],
            'department_id'       => ['nullable', 'integer', 'exists:departments,id'],
            'document_type'       => ['nullable', 'string'],
            'status'              => ['nullable', 'string', 'in:active,inactive'],
            'retention_policy_id' => ['nullable', 'integer', 'exists:retention_policies,id'],
            'retention_status'    => ['nullable', 'string'],
            'year'                => ['nullable', 'integer'],
        ]);

        try {
            $this->auditLogService->record(
                AuditAction::REPORT_EXPORTED_PDF,
                "Mengunduh Laporan Arsip versi PDF",
                null,
                ['filters' => array_filter($validated)]
            );

            return $this->reportService->exportPdf($validated);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
