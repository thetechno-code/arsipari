<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Arsip Digital — MTsN 1 Magelang</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0 0 2px 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin: 0 0 4px 0;
        }
        .header p {
            font-size: 9px;
            color: #6b7280;
            margin: 0;
        }
        .meta-box {
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 12px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            font-size: 9px;
            padding: 2px 0;
        }
        .summary-cards {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .summary-card {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 4px;
            text-align: center;
            padding: 6px;
        }
        .summary-card .number {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
        }
        .summary-card .label {
            font-size: 8px;
            color: #4b5563;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 4px;
            border: 1px solid #1e3a8a;
            text-align: left;
        }
        .data-table td {
            font-size: 8.5px;
            padding: 5px 4px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: monospace; }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 7.5px;
            font-weight: bold;
            border-radius: 2px;
            text-transform: uppercase;
        }
        .badge-green { background-color: #dcfce7; color: #166534; }
        .badge-red { background-color: #fee2e2; color: #991b1b; }
        .badge-blue { background-color: #dbeafe; color: #1e40af; }
        .footer {
            margin-top: 15px;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
            font-size: 8px;
            color: #9ca3af;
            width: 100%;
        }
    </style>
</head>
<body>

    {{-- KOP HEADER --}}
    <div class="header">
        <h1>LAPORAN ARSIP DIGITAL DOKUMEN</h1>
        <h2>MADRASAH TSANAWIYAH NEGERI 1 MAGELANG (ARSIPARI)</h2>
        <p>Sistem Pengelolaan & Manajemen Arsip Digital Internal Terpusat</p>
    </div>

    {{-- FILTER METADATA --}}
    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td width="15%"><strong>Periode Filter:</strong></td>
                <td width="35%">
                    @if($filterLabels['date_from'] || $filterLabels['date_to'])
                        {{ $filterLabels['date_from'] ?? 'Awal' }} s/d {{ $filterLabels['date_to'] ?? 'Sekarang' }}
                    @else
                        Semua Periode Tanggal
                    @endif
                </td>
                <td width="15%"><strong>Kategori Arsip:</strong></td>
                <td width="35%">{{ $filterLabels['category'] ?? 'Semua Kategori' }}</td>
            </tr>
            <tr>
                <td><strong>Unit / Bidang:</strong></td>
                <td>{{ $filterLabels['department'] ?? 'Semua Unit' }}</td>
                <td><strong>Status Operational:</strong></td>
                <td>{{ $filterLabels['status'] ?? 'Semua Status' }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal Cetak:</strong></td>
                <td colspan="3">{{ $generatedAt }}</td>
            </tr>
        </table>
    </div>

    {{-- AGGREGATE METRICS CARDS --}}
    <table class="summary-cards">
        <tr>
            <td width="16%">
                <div class="summary-card">
                    <div class="number">{{ $summary['total'] }}</div>
                    <div class="label">Total Arsip</div>
                </div>
            </td>
            <td width="16%">
                <div class="summary-card">
                    <div class="number" style="color:#166534;">{{ $summary['active'] }}</div>
                    <div class="label">Aktif</div>
                </div>
            </td>
            <td width="16%">
                <div class="summary-card">
                    <div class="number" style="color:#991b1b;">{{ $summary['inactive'] }}</div>
                    <div class="label">Tidak Aktif</div>
                </div>
            </td>
            <td width="16%">
                <div class="summary-card">
                    <div class="number" style="color:#1e40af;">{{ $summary['permanent'] }}</div>
                    <div class="label">Permanen</div>
                </div>
            </td>
            <td width="16%">
                <div class="summary-card">
                    <div class="number" style="color:#d97706;">{{ $summary['due_soon'] }}</div>
                    <div class="label">Akan Berakhir</div>
                </div>
            </td>
            <td width="16%">
                <div class="summary-card">
                    <div class="number" style="color:#dc2626;">{{ $summary['expired'] }}</div>
                    <div class="label">Telah Berakhir</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- DATA TABLE --}}
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%" class="text-center">No</th>
                <th width="12%">No. Arsip</th>
                <th width="12%">No. Dokumen</th>
                <th width="24%">Judul Arsip</th>
                <th width="12%">Kategori</th>
                <th width="10%">Unit / Bidang</th>
                <th width="8%" class="text-center">Tgl Dokumen</th>
                <th width="6%" class="text-center">Jenis</th>
                <th width="7%" class="text-center">Status</th>
                <th width="6%" class="text-center">Retensi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($archives as $index => $archive)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-mono" style="font-weight:bold;">{{ $archive->archive_number }}</td>
                <td class="font-mono">{{ $archive->document_number ?? '—' }}</td>
                <td><strong>{{ $archive->title }}</strong></td>
                <td>{{ $archive->category?->name ?? '—' }}</td>
                <td>{{ $archive->department?->name ?? '—' }}</td>
                <td class="text-center font-mono">{{ $archive->document_date ? $archive->document_date->format('d/m/Y') : '—' }}</td>
                <td class="text-center font-mono uppercase">{{ $archive->document_type }}</td>
                <td class="text-center">
                    @if($archive->status === 'active')
                        <span class="badge badge-green">Aktif</span>
                    @else
                        <span class="badge badge-red">Non-Aktif</span>
                    @endif
                </td>
                <td class="text-center">
                    <span class="badge {{ $archive->retention_status_badge }}">
                        {{ $archive->retention_status_label }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center" style="padding: 15px; color:#6b7280;">
                    Tidak ada data arsip yang memenuhi kriteria filter.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- FOOTER --}}
    <table class="footer">
        <tr>
            <td>ARSIPARI v{{ config('arsipari.version', '1.0.0') }} — Sistem Manajemen Arsip Digital MTsN 1 Magelang</td>
            <td class="text-right">Dicetak otomatis oleh sistem pada {{ $generatedAt }}</td>
        </tr>
    </table>

</body>
</html>
