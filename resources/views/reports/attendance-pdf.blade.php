<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Presensi Pegawai</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        .letterhead {
            width: 100%;
            height: auto;
            max-height: 80px;
            margin-bottom: 20px;
        }
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .periode {
            text-align: center;
            font-size: 12px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th {
            background-color: #E2E8F0;
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        table tr:nth-child(even) {
            background-color: #F7FAFC;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        .signature {
            margin-top: 60px;
            text-align: right;
            margin-right: 50px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-left: auto;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Kop Surat -->
    <img src="{{ public_path('kop.png') }}" class="letterhead" alt="Kop Surat">

    <!-- Title -->
    <div class="title">LAPORAN PRESENSI PEGAWAI</div>
    
    <!-- Periode -->
    <div class="periode">
        @if($startDate && $endDate)
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        @elseif($startDate)
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - Sekarang
        @else
            Periode: Semua Data
        @endif
    </div>

    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 10%;">NIP</th>
                <th style="width: 15%;">Nama</th>
                <th style="width: 15%;">Project</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 8%;">Check In</th>
                <th style="width: 8%;">Check Out</th>
                <th style="width: 8%;">Durasi</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $attendance->user->nip ?? '-' }}</td>
                    <td>{{ $attendance->user->name ?? '-' }}</td>
                    <td>{{ $attendance->project->nama_project ?? '-' }}</td>
                    <td>{{ $attendance->tanggal ? \Carbon\Carbon::parse($attendance->tanggal)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $attendance->check_in_time ?? '-' }}</td>
                    <td>{{ $attendance->check_out_time ?? '-' }}</td>
                    <td>{{ $attendance->duration_formatted ?? '-' }}</td>
                    <td>{{ $attendance->status_label ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Signature -->
    <div class="signature">
        <p>Mengetahui,</p>
        <br><br><br>
        <div class="signature-line"></div>
        <p>Admin</p>
    </div>
</body>
</html>
