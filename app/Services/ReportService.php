<?php

namespace App\Services;

use App\Models\Archive;
use App\Models\Category;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\RetentionPolicy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Exception;

class ReportService
{
    /**
     * Build reusable Archive query filtered by report parameters.
     */
    public function getFilteredQuery(array $filters): Builder
    {
        return Archive::query()
            ->with(['category', 'department', 'retentionPolicy', 'uploader'])
            ->filterCategory($filters['category_id'] ?? null)
            ->filterDepartment($filters['department_id'] ?? null)
            ->filterDocumentType($filters['document_type'] ?? null)
            ->filterStatus($filters['status'] ?? null)
            ->filterRetentionPolicy($filters['retention_policy_id'] ?? null)
            ->filterRetentionStatus($filters['retention_status'] ?? null)
            ->filterDateFrom($filters['date_from'] ?? null)
            ->filterDateTo($filters['date_to'] ?? null)
            ->filterYear($filters['year'] ?? null)
            ->sortBy($filters['sort'] ?? 'document_date', $filters['direction'] ?? 'desc');
    }

    /**
     * Get aggregate statistics for report summary card.
     */
    public function getReportSummary(array $filters): array
    {
        $baseQuery = Archive::query()
            ->filterCategory($filters['category_id'] ?? null)
            ->filterDepartment($filters['department_id'] ?? null)
            ->filterDocumentType($filters['document_type'] ?? null)
            ->filterRetentionPolicy($filters['retention_policy_id'] ?? null)
            ->filterDateFrom($filters['date_from'] ?? null)
            ->filterDateTo($filters['date_to'] ?? null)
            ->filterYear($filters['year'] ?? null);

        $total      = (clone $baseQuery)->count();
        $active     = (clone $baseQuery)->where('status', 'active')->count();
        $inactive   = (clone $baseQuery)->where('status', 'inactive')->count();

        // Retention aggregates
        $warningDays = (int) config('arsipari.retention_warning_days', 90);
        $today       = now()->startOfDay()->format('Y-m-d');
        $warningDate = now()->startOfDay()->addDays($warningDays)->format('Y-m-d');

        $permanent = (clone $baseQuery)->whereHas('retentionPolicy', fn($q) => $q->where('is_permanent', true))->count();
        $expired   = (clone $baseQuery)->whereNotNull('retention_until')->whereDate('retention_until', '<', $today)->count();
        $dueSoon   = (clone $baseQuery)->whereNotNull('retention_until')
            ->whereDate('retention_until', '>=', $today)
            ->whereDate('retention_until', '<=', $warningDate)
            ->count();

        return [
            'total'     => $total,
            'active'    => $active,
            'inactive'  => $inactive,
            'permanent' => $permanent,
            'due_soon'  => $dueSoon,
            'expired'   => $expired,
        ];
    }

    /**
     * Get paginated archive records for web preview.
     */
    public function getPaginatedReport(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->getFilteredQuery($filters)->paginate($perPage)->withQueryString();
    }

    /**
     * Export archive report to Excel (.xlsx).
     */
    public function exportExcel(array $filters): StreamedResponse
    {
        $archives = $this->getFilteredQuery($filters)->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Arsip Digital');

        // Document Title Header
        $sheet->setCellValue('A1', 'LAPORAN ARSIP DIGITAL');
        $sheet->setCellValue('A2', 'MTsN 1 MAGELANG');
        $sheet->setCellValue('A3', 'Dicetak pada: ' . now()->translatedFormat('d F Y H:i'));

        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10);

        // Filter Meta Row
        $filterMeta = [];
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $from = !empty($filters['date_from']) ? date('d/m/Y', strtotime($filters['date_from'])) : 'Awal';
            $to   = !empty($filters['date_to']) ? date('d/m/Y', strtotime($filters['date_to'])) : 'Sekarang';
            $filterMeta[] = "Periode: {$from} - {$to}";
        }
        if (!empty($filters['category_id'])) {
            $cat = Category::find($filters['category_id']);
            if ($cat) $filterMeta[] = "Kategori: {$cat->name}";
        }
        if (!empty($filters['department_id'])) {
            $dept = Department::find($filters['department_id']);
            if ($dept) $filterMeta[] = "Unit: {$dept->name}";
        }
        if (!empty($filters['status'])) {
            $filterMeta[] = "Status: " . ucfirst($filters['status']);
        }

        if (count($filterMeta) > 0) {
            $sheet->setCellValue('A4', implode(' | ', $filterMeta));
            $sheet->getStyle('A4')->getFont()->setSize(10)->setBold(true);
        }

        // Table Header
        $headers = [
            'A5' => 'No',
            'B5' => 'Nomor Arsip',
            'C5' => 'Nomor Dokumen',
            'D5' => 'Judul Arsip',
            'E5' => 'Kategori',
            'F5' => 'Unit / Bidang',
            'G5' => 'Tanggal Dokumen',
            'H5' => 'Tahun',
            'I5' => 'Jenis Dokumen',
            'J5' => 'Status Operational',
            'K5' => 'Kebijakan Retensi',
            'L5' => 'Masa Retensi Sampai',
            'M5' => 'Diupload Oleh',
            'N5' => 'Tanggal Upload',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style Table Header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A8A'], // Primary navy blue
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A5:N5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(25);

        // Fill Rows
        $row = 6;
        foreach ($archives as $index => $archive) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $archive->archive_number);
            $sheet->setCellValue('C' . $row, $archive->document_number ?? '—');
            $sheet->setCellValue('D' . $row, $archive->title);
            $sheet->setCellValue('E' . $row, $archive->category?->name ?? '—');
            $sheet->setCellValue('F' . $row, $archive->department?->name ?? '—');
            $sheet->setCellValue('G' . $row, $archive->document_date ? $archive->document_date->format('d/m/Y') : '—');
            $sheet->setCellValue('H' . $row, $archive->year);
            $sheet->setCellValue('I' . $row, strtoupper($archive->document_type));
            $sheet->setCellValue('J' . $row, $archive->status === 'active' ? 'Aktif' : 'Tidak Aktif');
            $sheet->setCellValue('K' . $row, $archive->retentionPolicy?->name ?? 'Permanen');
            $sheet->setCellValue('L' . $row, $archive->retention_until ? $archive->retention_until->format('d/m/Y') : 'Permanen');
            $sheet->setCellValue('M' . $row, $archive->uploader?->name ?? 'Sistem');
            $sheet->setCellValue('N' . $row, $archive->created_at ? $archive->created_at->format('d/m/Y H:i') : '—');

            // Alignment
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('L' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Auto column widths
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Freeze header row
        $sheet->freezePane('A6');

        $filename = 'laporan-arsip-' . date('Y-m-d-His') . '.xlsx';

        return response()->stream(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Cache-Control'       => 'max-age=0',
            ]
        );
    }

    /**
     * Export archive report to PDF (.pdf).
     */
    public function exportPdf(array $filters)
    {
        $maxRows = (int) config('arsipari.pdf_max_rows', 5000);
        $count   = $this->getFilteredQuery($filters)->count();

        if ($count > $maxRows) {
            throw new Exception("Jumlah data ({$count} arsip) melebihi batas maksimal PDF ({$maxRows} arsip). Silakan gunakan Export Excel untuk data besar.");
        }

        $archives = $this->getFilteredQuery($filters)->get();
        $summary  = $this->getReportSummary($filters);

        // Fetch label metadata for filter header
        $filterLabels = [
            'category'   => !empty($filters['category_id']) ? Category::find($filters['category_id'])?->name : null,
            'department' => !empty($filters['department_id']) ? Department::find($filters['department_id'])?->name : null,
            'status'     => !empty($filters['status']) ? ucfirst($filters['status']) : null,
            'date_from'  => !empty($filters['date_from']) ? date('d/m/Y', strtotime($filters['date_from'])) : null,
            'date_to'    => !empty($filters['date_to']) ? date('d/m/Y', strtotime($filters['date_to'])) : null,
        ];

        $pdf = Pdf::loadView('reports.pdf', [
            'archives'     => $archives,
            'summary'      => $summary,
            'filterLabels' => $filterLabels,
            'generatedAt'  => now()->translatedFormat('d F Y H:i'),
        ]);

        $pdf->setPaper('a4', 'landscape');

        $filename = 'laporan-arsip-' . date('Y-m-d-His') . '.pdf';

        return $pdf->download($filename);
    }
}
