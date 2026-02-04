<!DOCTYPE html>
<html>

<head>
    <title>Jadwal Perkuliahan</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 7pt;
            color: #000;
            line-height: 1.1;
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

        /* --- JUDUL HALAMAN --- */
        .info-jadwal {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 10pt;
            text-transform: uppercase;
            text-decoration: underline;
        }

        /* --- TABEL MATRIKS --- */
        .table-matrix {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 20px;
        }

        .table-matrix th,
        .table-matrix td {
            border: 0.5pt solid #000;
            padding: 2px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow: hidden;
            font-size: 7pt;
        }

        /* HEADER TABEL */
        .table-matrix th {
            background-color: #f2ff00;
            /* Yellow Header */
            font-weight: bold;
            text-align: center;
            height: 25px;
            text-transform: uppercase;
        }

        /* KOLOM HARI */
        .col-hari {
            width: 4%;
            background-color: #f2ff00;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
        }

        .vertical-text {
            /* Membuat teks miring 90 derajat vertikal dengan aman */
            transform: rotate(-90deg);
            white-space: nowrap;
            display: block;
            width: 15px;
            /* Fixed width to prevent overflow */
            margin: 0 auto;
            text-align: center;
            font-size: 7pt;
        }

        /* KOLOM JAM */
        .col-jam {
            width: 8%;
            text-align: center;
            font-weight: bold;
            background-color: #fcfcfc;
        }

        /* ISI CELL (JADWAL) */
        .filled {
            background-color: #fff;
            padding: 2px;
            vertical-align: top;
        }

        .course-name {
            font-weight: bold;
            display: block;
            margin-bottom: 1px;
            font-size: 7pt;
        }

        .class-info {
            display: block;
            font-size: 6.5pt;
            color: #333;
            margin-bottom: 1px;
        }

        .class-badge {
            display: inline-block;
            border: 0.3px solid #666;
            padding: 0px 2px;
            border-radius: 2px;
            font-weight: bold;
            margin-right: 3px;
            margin-top: 5px;
            background-color: #f9f9f9;
        }

        .lecturer-name {
            font-style: italic;
            display: block;
            font-size: 6.5pt;
            margin-top: 1px;
        }

        /* --- TANDA TANGAN --- */
        .signature-table {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
            font-size: 9pt;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    @foreach ($roomChunks as $chunkIndex => $rooms)
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

        <div style="text-align: center; margin-bottom: 20px;">
            <h3 style="margin:0; text-decoration: underline; text-transform: uppercase;">Jadwal Perkuliahan
                {{ strtoupper(str_replace('_', ' ', $campus)) }}</h3>
            <span style="font-size: 11px;"> Kelas {{ strtoupper($shift) }} - Tahun Akademik
                {{ strtoupper($activePeriod->name) }}</span>
        </div>

        <table class="table-matrix">
            <thead>
                <tr>
                    {{-- Header HARI & JAM --}}
                    <th class="col-hari">HARI</th>
                    <th class="col-jam">JAM</th>

                    {{-- Header Ruangan --}}
                    @foreach ($rooms as $room)
                        <th>
                            {{ $room->name }}<br>
                            <span style="font-weight: normal; font-size: 6pt;">
                                ({{ $room->building }} - {{ $room->capacity }} Kursi)
                            </span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    $daysIndo = [
                        'Monday' => 'SENIN',
                        'Tuesday' => 'SELASA',
                        'Wednesday' => 'RABU',
                        'Thursday' => 'KAMIS',
                        'Friday' => 'JUMAT',
                        'Saturday' => 'SABTU',
                    ];
                @endphp

                @foreach ($days as $day)
                    @foreach ($masterSlots as $slotIndex => $slotTime)
                        <tr>
                            @php $middleIndex = floor($loop->count / 2) - 1; @endphp

                            @if ($slotIndex === 0)
                                <td class="col-hari" style="border-bottom: none;">
                                </td>
                            @elseif ($loop->last)
                                <td class="col-hari" style="border-top: none;">
                                </td>
                            @else
                                <td class="col-hari" style="border-top: none; border-bottom: none;">
                                    @if ($slotIndex == $middleIndex)
                                        <div class="vertical-text">{{ $daysIndo[$day] }}</div>
                                    @endif
                                </td>
                            @endif

                            <td class="col-jam">
                                {{ $slotTime[0] }} - {{ $slotTime[1] }}
                            </td>

                            @foreach ($rooms as $room)
                                @php
                                    $schedules = $scheduleMatrix[$day][$slotIndex][$room->id] ?? [];
                                @endphp

                                @if (!empty($schedules))
                                    <td class="filled">
                                        @foreach (collect($schedules)->groupBy('course_id') as $courseId => $groupedScheds)
                                            @php
                                                $first = $groupedScheds->first();
                                            @endphp

                                            <span class="course-name">{{ $first->course->name ?? '-' }}</span>

                                            <div class="class-info">
                                                @foreach ($groupedScheds as $sch)
                                                    <span class="class-badge">
                                                        {{ $sch->studyClass->full_name ?? ($sch->studyClass->name ?? '-') }}
                                                    </span>
                                                @endforeach
                                            </div>

                                            <span class="lecturer-name">
                                                {{ \Illuminate\Support\Str::limit($first->lecturer->name ?? 'Belum ada', 25) }}
                                            </span>

                                            @if (!$loop->last)
                                                <hr style="border: 0; border-top: 0.5px dashed #000; margin: 2px 0;">
                                            @endif
                                        @endforeach
                                    </td>
                                @else
                                    <td></td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach

                    @if (!$loop->last)
                        <tr>
                            <td colspan="{{ count($rooms) + 2 }}"
                                style="background-color: #000; height: 1px; padding:0; border:none;"></td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        {{-- TANDA TANGAN (Halaman Terakhir) --}}
        @if ($loop->last)
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
        @endif

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

</body>

</html>
