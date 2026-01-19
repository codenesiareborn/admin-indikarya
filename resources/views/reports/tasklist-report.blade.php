<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Task List</title>
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
        .rate-high { color: #16a34a; }
        .rate-medium { color: #d97706; }
        .rate-low { color: #dc2626; }
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
        <h1>LAPORAN TASK LIST</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
        <p>No. Laporan: {{ $reportNumber }}</p>
    </div>

    <div class="summary">
        <h3>📊 RINGKASAN</h3>
        <table style="width: auto; margin: 0;">
            <tr>
                <td style="border: none; padding: 3px 15px;">Total Submit: <strong>{{ $stats['total_submissions'] }}</strong></td>
                <td style="border: none; padding: 3px 15px;">Task Selesai: <strong class="rate-high">{{ $stats['total_completed'] }}</strong></td>
                <td style="border: none; padding: 3px 15px;">Task Pending: <strong class="rate-medium">{{ $stats['total_pending'] }}</strong></td>
                <td style="border: none; padding: 3px 15px;">Completion Rate: <strong>{{ $stats['completion_rate'] }}%</strong></td>
                <td style="border: none; padding: 3px 15px;">Pegawai Aktif: <strong>{{ $stats['active_employees'] }}</strong></td>
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
                <th>Area</th>
                <th style="width: 80px;">Tanggal</th>
                <th style="width: 60px;">Jam</th>
                <th style="width: 50px;">Task</th>
                <th style="width: 50px;">%</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $submission)
                @php
                    $rateClass = $submission->completion_rate >= 100 ? 'rate-high' : ($submission->completion_rate >= 50 ? 'rate-medium' : 'rate-low');
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $submission->employee->nip ?? '-' }}</td>
                    <td>{{ $submission->employee->nama_lengkap ?? '-' }}</td>
                    <td>{{ $submission->project->nama_project ?? '-' }}</td>
                    <td>{{ $submission->room->nama_ruangan ?? '-' }}</td>
                    <td>{{ $submission->tanggal?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $submission->submitted_at?->format('H:i') ?? '-' }}</td>
                    <td>{{ $submission->completed_count }}/{{ $submission->total_tasks }}</td>
                    <td class="{{ $rateClass }}">{{ $submission->completion_rate }}%</td>
                    <td>{{ $submission->catatan ?? '-' }}</td>
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
