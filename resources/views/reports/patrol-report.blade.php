<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Patroli</title>
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
            max-height: 60px;
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
        .footer {
            text-align: right;
            font-size: 9px;
            color: #888;
            margin-top: 20px;
        }
        .status-aman { color: #16a34a; font-weight: bold; }
        .status-bahaya { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        @if(!empty($settings['company_logo']))
            <img src="{{ public_path('storage/' . $settings['company_logo']) }}" class="logo" alt="Logo">
        @endif
        <div class="company-name">{{ $settings['company_name'] ?? 'PT Indikarya' }}</div>
        <div class="company-info">
            {{ $settings['company_address'] ?? '' }}<br>
            Tel: {{ $settings['company_phone'] ?? '-' }} | Email: {{ $settings['company_email'] ?? '-' }}
        </div>
    </div>

    <div class="report-title">
        <h1>LAPORAN PATROLI</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
        <p>No. Laporan: {{ $reportNumber }}</p>
    </div>

    <div class="summary">
        <h3>📊 RINGKASAN</h3>
        <table style="width: auto; margin: 0;">
            <tr>
                <td style="border: none; padding: 3px 15px;">Total Patroli: <strong>{{ $stats['total'] }}</strong></td>
                <td style="border: none; padding: 3px 15px;">Aman: <strong class="status-aman">{{ $stats['aman'] }}</strong></td>
                <td style="border: none; padding: 3px 15px;">Tidak Aman: <strong class="status-bahaya">{{ $stats['tidak_aman'] }}</strong></td>
                <td style="border: none; padding: 3px 15px;">% Aman: <strong>{{ $stats['presentase'] }}%</strong></td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 80px;">NIP</th>
                <th>Nama Petugas</th>
                <th>Project</th>
                <th>Area</th>
                <th style="width: 80px;">Tanggal</th>
                <th style="width: 60px;">Waktu</th>
                <th style="width: 80px;">Status</th>
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
                    <td class="{{ $patrol->status === 'Aman' ? 'status-aman' : 'status-bahaya' }}">
                        {{ $patrol->status }}
                    </td>
                    <td>{{ $patrol->note ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d M Y H:i:s') }}
    </div>
</body>
</html>
