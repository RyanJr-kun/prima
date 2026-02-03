<!DOCTYPE html>
<html>

<head>
    <title>Cetak Kalender Akademik</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
        }

        /* LAYOUT HEADER */
        .header-wrapper {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .header-content-table {
            margin: 0 auto;
            width: auto;
        }

        .logo {
            width: 70px;
            height: auto;
            margin-right: 15px;
        }

        .kampus-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            text-align: left;
        }

        .sk-text,
        .alamat {
            margin: 1px 0;
            text-align: left;
        }

        .sk-text {
            font-size: 12px;
        }

        .alamat {
            font-size: 12px;
        }

        /* DATA TABLE */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }

        .data-table th {
            text-align: center;
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    {{-- KOP SURAT --}}
    <div class="header-wrapper">
        <table class="header-content-table">
            <tr>
                <td style="vertical-align: middle;">
                    {{-- Pastikan path logo benar --}}
                    <img src="{{ public_path('assets/img/logo.png') }}" class="logo">
                </td>
                <td style="vertical-align: middle;">
                    <h1 class="kampus-name">POLITEKNIK INDONUSA SURAKARTA</h1>
                    <p class="sk-text">SK. Mendiknas RI No. 166/D/O/2002</p>
                    <p class="alamat">
                        Kampus 1: Jl. KH. Samanhudi No. 31, Sondakan, Laweyan, Surakarta <br>
                        Kampus 2: Jl. Palaraya No. 5, Cemani, Grogol, Sukoharjo <br>
                        Website: www.poltekindonusa.ac.id
                    </p>
                </td>
            </tr>
        </table>
    </div>

    {{-- JUDUL DOKUMEN --}}
    <div style="text-align: center; margin-bottom: 20px;">
        <h3 style="margin:0; text-decoration: underline; text-transform: uppercase;">KALENDER AKADEMIK</h3>
        <span style="font-size: 11px;">Tahun Akademik {{ $tahunAkademik }}</span>
    </div>

    {{-- TABEL KEGIATAN --}}
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="27%">Nama Kegiatan</th>
                <th class="text-center" width="22%">Tanggal</th>
                <th width="48%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $index => $event)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $event->name }}</td>
                    <td class="text-center">
                        <span>{{ $event->start_date->format('d M Y') }} s/d
                            {{ $event->end_date->format('d M Y') }}</span>
                    </td>
                    {{-- <td class="text-center">
                        @if (empty($event->target_semesters))
                            Semua Semester
                        @else
                            Sem {{ implode(', ', $event->target_semesters) }}
                        @endif
                    </td> --}}
                    <td>{{ $event->description ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px;">Tidak ada data kegiatan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TANDA TANGAN (Hanya Wadir 1 & Direktur) --}}
    <table width="100%" style="margin-top: 30px; page-break-inside: avoid;">
        <tr>
            {{-- Spasi Kiri Kosong --}}
            <td width="40%"></td>
            {{-- Direktur --}}
            <td width="30%" class="text-center" style="vertical-align: top;">

                Menyetujui,<br>
                Direktur
                <br><br>
                @if ($direktur && !empty($direktur->signature_path) && file_exists(public_path('storage/' . $direktur->signature_path)))
                    <img src="{{ public_path('storage/' . $direktur->signature_path) }}"
                        style="height: 90px; width: auto; margin-top: -15px; margin-bottom: -10px;">
                    <br>
                @else
                    <br><br><br>
                @endif
                <strong><u>{{ $direktur->name ?? '.........................' }}</u></strong> <br>
                NIDN. {{ $direktur->nidn ?? '-' }}
            </td>

            {{-- Wadir 1 --}}
            <td width="30%" class="text-center" style="vertical-align: top;">
                Surakarta, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }} <br>
                Wakil Direktur I
                <br><br>
                @if ($wadir1 && !empty($wadir1->signature_path) && file_exists(public_path('storage/' . $wadir1->signature_path)))
                    <img src="{{ public_path('storage/' . $wadir1->signature_path) }}"
                        style="height: 90px; width: auto; margin-top: -15px; margin-bottom: -10px;">
                    <br>
                @else
                    <br><br><br>
                @endif
                <strong><u>{{ $wadir1->name ?? '.........................' }}</u></strong> <br>
                NIDN. {{ $wadir1->nidn ?? '-' }}
            </td>
        </tr>
    </table>

</body>

</html>
