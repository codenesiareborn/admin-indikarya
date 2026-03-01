<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Patroli</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            position: relative;
        }
        .logo {
            max-height: 120px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .company-address {
            font-size: 11px;
            color: #666;
        }
        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }
        .meta-info {
            width: 100%;
            margin-bottom: 20px;
        }
        .meta-info td {
            padding: 5px;
            vertical-align: top;
        }
        .stats-container {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .stat-box {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            background-color: #f9f9f9;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
            display: block;
            margin-top: 5px;
        }
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 11px;
            color: #666;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('kop.png')))
            <img src="{{ public_path('kop.png') }}" class="logo" alt="Kop Surat" style="max-width: 100%; height: auto; max-height: 120px;">
        @else
            <div class="company-name">{{ $settings['company_name'] ?? 'PT. Indikarya' }}</div>
            <div class="company-address">{{ $settings['company_address'] ?? 'Jalan Raya No. 123, Jakarta' }}</div>
        @endif
    </div>

    <div class="report-title">LAPORAN PATROLI</div>

    <table class="meta-info">
        <tr>
            <td width="120"><strong>Nomor Dokumen</strong></td>
            <td width="10">:</td>
            <td>{{ $reportNumber }}</td>
            <td width="120"><strong>Periode</strong></td>
            <td width="10">:</td>
            <td>{{ $startDate }} - {{ $endDate }}</td>
        </tr>
        <tr>
            <td><strong>Tanggal Cetak</strong></td>
            <td>:</td>
            <td>{{ now()->format('d/m/Y H:i') }}</td>
            <td><strong>Dicetak Oleh</strong></td>
            <td>:</td>
            <td>{{ auth()->user()->name ?? 'Administrator' }}</td>
        </tr>
    </table>

    <table class="stats-container">
        <tr>
            <td class="stat-box">
                <span class="stat-label">Total Patroli</span>
                <span class="stat-value">{{ $stats['total'] }}</span>
            </td>
            <td class="stat-box">
                <span class="stat-label">Aman</span>
                <span class="stat-value">{{ $stats['aman'] }}</span>
            </td>
            <td class="stat-box">
                <span class="stat-label">Tidak Aman</span>
                <span class="stat-value">{{ $stats['tidak_aman'] }}</span>
            </td>
            <td class="stat-box">
                <span class="stat-label">Persentase Aman</span>
                <span class="stat-value">{{ $stats['presentase'] }}%</span>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>NIP</th>
                <th>Nama Petugas</th>
                <th>Project</th>
                <th>Area</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $patrol)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $patrol->user->nip ?? '-' }}</td>
                    <td>{{ $patrol->user->name ?? '-' }}</td>
                    <td>{{ $patrol->project->nama_project ?? '-' }}</td>
                    <td>{{ $patrol->area_name ?? '-' }}</td>
                    <td>{{ $patrol->patrol_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $patrol->patrol_time ? date('H:i', strtotime($patrol->patrol_time)) : '-' }}</td>
                    <td>{{ $patrol->status }}</td>
                    <td>{{ $patrol->note ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px;">Tidak ada data patroli pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem Informasi Management Project
    </div>
</body>
</html>
