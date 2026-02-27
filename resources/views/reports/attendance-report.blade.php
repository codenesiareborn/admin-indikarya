<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Presensi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .logo {
            max-height: 120px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 10px;
            color: #666;
        }
        .report-title {
            text-align: center;
            margin: 20px 0;
        }
        .report-title h1 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        .report-title p {
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background: #fafafa;
        }
        .summary {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary h3 {
            margin-bottom: 10px;
            font-size: 12px;
        }
        .summary-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .summary-item {
            background: white;
            padding: 8px 15px;
            border-radius: 3px;
            border: 1px solid #ddd;
        }
        .footer {
            text-align: right;
            font-size: 9px;
            color: #888;
            margin-top: 20px;
        }
        .status-hadir { color: #16a34a; }
        .status-terlambat { color: #d97706; }
        .status-izin { color: #2563eb; }
        .status-sakit { color: #dc2626; }
        .status-alpha { color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('kop.png')))
            <img src="{{ public_path('kop.png') }}" class="logo" alt="Kop Surat" style="max-width: 100%; height: auto; max-height: 120px;">
        @else
            <div class="company-name">{{ $settings['company_name'] ?? 'PT Indikarya' }}</div>
            <div class="company-info">
                {{ $settings['company_address'] ?? '' }}<br>
                Tel: {{ $settings['company_phone'] ?? '-' }} | Email: {{ $settings['company_email'] ?? '-' }}
            </div>
        @endif
    </div>

    <div class="report-title">
        <h1>LAPORAN PRESENSI PEGAWAI</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
        <p>No. Laporan: {{ $reportNumber }}</p>
    </div>

    <div class="summary">
        <h3>📊 RINGKASAN</h3>
        <table style="width: auto; margin: 0;">
            <tr>
                <td style="border: none; padding: 3px 15px;">Total Data: <strong>{{ $stats['total'] }}</strong></td>
                <td style="border: none; padding: 3px 15px;">Hadir: <strong class="status-hadir">{{ $stats['hadir'] }}</strong></td>
                <td style="border: none; padding: 3px 15px;">Terlambat: <strong class="status-terlambat">{{ $stats['terlambat'] }}</strong></td>
                <td style="border: none; padding: 3px 15px;">Izin: <strong class="status-izin">{{ $stats['izin'] }}</strong></td>
                <td style="border: none; padding: 3px 15px;">Sakit: <strong class="status-sakit">{{ $stats['sakit'] }}</strong></td>
                <td style="border: none; padding: 3px 15px;">Alpha: <strong class="status-alpha">{{ $stats['alpha'] }}</strong></td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 70px;">NIP</th>
                <th>Nama Pegawai</th>
                <th>Project</th>
                <th style="width: 70px;">Shift</th>
                <th style="width: 80px;">Tanggal</th>
                <th style="width: 60px;">Masuk</th>
                <th style="width: 60px;">Keluar</th>
                <th style="width: 70px;">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $attendance)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $attendance->employee->nip ?? '-' }}</td>
                    <td>{{ $attendance->employee->name ?? '-' }}</td>
                    <td>{{ $attendance->project->nama_project ?? '-' }}</td>
                    <td>{{ $attendance->shift_name_display ?? '-' }}</td>
                    <td>{{ $attendance->tanggal?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $attendance->check_in?->format('H:i') ?? '-' }}</td>
                    <td>{{ $attendance->check_out?->format('H:i') ?? '-' }}</td>
                    <td class="status-{{ $attendance->status }}">{{ $attendance->status_label ?? '-' }}</td>
                    <td>{{ $attendance->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d M Y H:i:s') }}
    </div>
</body>
</html>
