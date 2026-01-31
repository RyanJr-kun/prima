<!DOCTYPE html>
<html>

<head>
    <title>Cetak Distribusi Mata Kuliah</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
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

        /* INFO TABLE */
        .info-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 12px;
        }

        .info-table td {
            vertical-align: top;
            padding: 1px 0;
        }

        .label-col {
            width: 12%;
            font-weight: bold;
        }

        .sep-col {
            width: 2%;
        }

        .val-col {
            width: 86%;
        }

        /* DATA TABLE */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 4px;
        }

        .data-table th {
            text-align: center;
            font-weight: bold;
            vertical-align: middle;
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

        .total-row {
            font-weight: bold;
        }
    </style>
</head>

<body>

    {{-- KOP SURAT --}}
    <div class="header-wrapper">
        <table class="header-content-table">
            <tr>
                <td style="vertical-align: middle;">
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

    <div style="text-align: center; margin-bottom: 16px;">
        <h3 style="margin:0; text-decoration: underline; text-transform: uppercase;">{{ $doc->type_label }}</h3>
        <span style="font-size: 12px;">Tahun Akademik {{ $tahunAkademik }}</span>
    </div>

    {{-- LOOP 1: PER SEMESTER --}}
    @foreach ($dataIsi as $semester => $itemsBySemester)
        @php
            // STEP 1: PISAHKAN DATA BERDASARKAN SHIFT (MIRIP CONTROLLER)
            // Ini memastikan data 'Pagi' terisolasi dari data 'Malam'
            $itemsByShift = $itemsBySemester->groupBy(function ($item) {
                return ucfirst($item->studyClass->shift);
            });

            $romawi = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII'];
            $semRomawi = $romawi[$semester] ?? $semester;
        @endphp

        {{-- LOOP 2: PER SHIFT (Agar tabel Pagi & Malam terpisah halamannya/bloknya) --}}
        @foreach ($itemsByShift as $shiftName => $items)
            @php
                $firstItem = $items->first();
                $angkatan = $firstItem->studyClass->angkatan ?? '-';
                $uniqueClasses = $items->pluck('studyClass')->unique('id'); // List Kelas di shift ini
                $kurikulum = $firstItem->course->kurikulum->name ?? '2024';

                // STEP 2: GROUP MATKUL (Gabungkan Kelas A & B jika shiftnya sama)
                $uniqueCourses = $items->groupBy('course_id');

                // Init Total SKS
                $sumT = 0;
                $sumP = 0;
                $sumL = 0;
                $sumTotal = 0;
            @endphp

            {{-- HEADER INFO --}}
            <table class="info-table">
                <tr>
                    <td class="label-col">Semester</td>
                    <td class="sep-col">:</td>
                    <td class="val-col">{{ $semRomawi }} ({{ $shiftName }})</td>
                </tr>
                <tr>
                    <td class="label-col">Angkatan</td>
                    <td class="sep-col">:</td>
                    <td class="val-col">{{ $angkatan }}</td>
                </tr>
                <tr>
                    <td class="label-col">Kelas</td>
                    <td class="sep-col">:</td>
                    <td class="val-col">
                        <table style="width: 100%; border-collapse: collapse;">
                            @foreach ($uniqueClasses as $kls)
                                <tr>
                                    <td width="30px" style="font-weight: bold;">{{ $kls->name }}</td>
                                    <td width="10px">:</td>
                                    <td>
                                        {{ $kls->total_students }}
                                        @if ($kls->academicAdvisor)
                                            ({{ $kls->academicAdvisor->name_with_title ?? $kls->academicAdvisor->name }})
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="label-col">Kurikulum</td>
                    <td class="sep-col">:</td>
                    <td class="val-col">{{ $kurikulum }}</td>
                </tr>
            </table>

            {{-- TABEL DATA --}}
            <table class="data-table">
                <thead>
                    <tr>
                        <th rowspan="2" width="3%">No</th>
                        <th rowspan="2" width="8%">Kode</th>
                        <th rowspan="2" width="24%">Mata Kuliah</th>
                        <th colspan="4" width="12%">SKS</th>
                        <th rowspan="2" width="19%">Dosen Pengampu (Real)</th>
                        <th rowspan="2" width="19%">Dosen Team (PDDIKTI)</th>
                        <th rowspan="2" width="7%">Ref</th>
                        <th rowspan="2" width="8%">Luaran</th>
                    </tr>
                    <tr>
                        <th width="3%">T</th>
                        <th width="3%">P</th>
                        <th width="3%">L</th>
                        <th width="3%">JML</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @foreach ($uniqueCourses as $courseId => $dists)
                        @php
                            $dist = $dists->first();
                            $course = $dist->course;

                            $sumT += $course->sks_teori;
                            $sumP += $course->sks_praktik;
                            $sumL += $course->sks_lapangan;
                            $sumTotal += $course->sksTotal;

                            // --- LOGIKA DOSEN (MATCHING WEB) ---
                            // Menggunakan data dari item pertama saja ($dist) agar sama dengan tampilan Web
                            $realMap = [];
                            $pddiktiMap = [];

                            // A. Koordinator
                            if ($dist->user) {
                                $realMap[] = $dist->user->name . ' (Koord)';
                            }

                            // B. Pivot Team
                            foreach ($dist->teachingLecturers as $tl) {
                                // Jika ID Dosen == ID Koordinator, skip (sudah tercatat sbg koord)
                                if ($dist->user_id && $tl->id == $dist->user_id) {
                                    continue;
                                }
                                $realMap[] = $tl->name;
                            }

                            // C. PDDIKTI
                            foreach ($dist->pddiktiLecturers as $pl) {
                                $pddiktiMap[] = $pl->name;
                            }

                            // Gabungkan jadi string
                            $strReal = implode('<br>', $realMap);
                            $strPddikti = implode('<br>', $pddiktiMap);
                        @endphp
                        <tr>
                            <td class="text-center">{{ $no++ }}</td>
                            <td class="text-center">{{ $course->code }}</td>
                            <td>{{ $course->name }}</td>
                            <td class="text-center">{{ $course->sks_teori ?: '-' }}</td>
                            <td class="text-center">{{ $course->sks_praktik ?: '-' }}</td>
                            <td class="text-center">{{ $course->sks_lapangan ?: '-' }}</td>
                            <td class="text-center font-bold">
                                {{ $course->sksTotal }}</td>

                            <td>{!! $strReal ?: '-' !!}</td>
                            <td>{!! $strPddikti ?: '-' !!}</td>

                            <td>{{ $dist->referensi ?: '-' }}</td>
                            <td>{{ $dist->luaran ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3" class="text-right">JUMLAH SKS</td>
                        <td class="text-center">{{ $sumT }}</td>
                        <td class="text-center">{{ $sumP }}</td>
                        <td class="text-center">{{ $sumL }}</td>
                        <td class="text-center">{{ $sumTotal }}</td>
                        <td colspan="4" style="border:none;"></td>
                    </tr>
                </tfoot>
            </table>

            <br>
            {{-- Page Break Logic --}}
            @if (!($loop->parent->last && $loop->last))
                <div class="page-break"></div>
            @endif
        @endforeach
    @endforeach

    @php
        // Ambil data pejabat untuk TTD
        $wadir1 = \App\Models\User::role('wadir1')->first();
        $wadir2 = \App\Models\User::role('wadir2')->first();
        $direktur = \App\Models\User::role('direktur')->first();

        // Ambil Kaprodi dari relasi prodi dokumen
        $kaprodi = null;
        if ($doc->prodi && $doc->prodi->kaprodi_id) {
            $kaprodi = \App\Models\User::find($doc->prodi->kaprodi_id);
        }
    @endphp

    <table width="100%" style="margin-top: 30px; page-break-inside: avoid;">
        <tr>
            {{-- Wadir 1 --}}
            <td width="33%" class="text-center" style="vertical-align: top;">
                Mengetahui,<br>
                Wakil Direktur I
                <br><br>
                @if ($wadir1 && !empty($wadir1->signature_path) && file_exists(public_path('storage/' . $wadir1->signature_path)))
                    <img src="{{ public_path('storage/' . $wadir1->signature_path) }}"
                        style="height: 90px; width: auto; margin-top: -25px; margin-bottom: -20px;">
                    <br>
                @else
                    <br><br><br><br>
                @endif
                <strong><u>{{ $wadir1->name ?? '.........................' }}</u></strong> <br>
                NIDN. {{ $wadir1->nidn ?? '-' }}
            </td>

            {{-- Wadir 2 --}}
            <td width="33%" class="text-center" style="vertical-align: top;">
                Mengetahui,<br>
                Wakil Direktur II
                <br><br>
                @if ($wadir2 && !empty($wadir2->signature_path) && file_exists(public_path('storage/' . $wadir2->signature_path)))
                    <img src="{{ public_path('storage/' . $wadir2->signature_path) }}"
                        style="height: 90px; width: auto; margin-top: -25px; margin-bottom: -20px;">
                    <br>
                @else
                    <br><br><br><br>
                @endif
                <strong><u>{{ $wadir2->name ?? '.........................' }}</u></strong> <br>
                NIDN. {{ $wadir2->nidn ?? '-' }}
            </td>

            {{-- Kaprodi --}}
            <td width="33%" class="text-center" style="vertical-align: top;">
                Surakarta, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }} <br>
                Ketua Program Studi
                <br><br>
                @if ($kaprodi && !empty($kaprodi->signature_path) && file_exists(public_path('storage/' . $kaprodi->signature_path)))
                    <img src="{{ public_path('storage/' . $kaprodi->signature_path) }}"
                        style="height: 90px; width: auto; margin-top: -25px; margin-bottom: -20px;">
                    <br>
                @else
                    <br><br><br><br>
                @endif
                <strong><u>{{ $kaprodi->name ?? '.........................' }}</u></strong> <br>
                NIDN. {{ $kaprodi->nidn ?? '-' }}
            </td>
        </tr>
    </table>

    {{-- Direktur --}}
    <table width="100%" style="margin-top: 10px; page-break-inside: avoid;">
        <tr>
            <td class="text-center">
                Menyetujui,<br>
                Direktur
                <br><br>
                @if ($direktur && !empty($direktur->signature_path) && file_exists(public_path('storage/' . $direktur->signature_path)))
                    <img src="{{ public_path('storage/' . $direktur->signature_path) }}"
                        style="height: 90px; width: auto; margin-top: -25px; margin-bottom: -20px;">
                    <br>
                @else
                    <br><br><br><br>
                @endif
                <strong><u>{{ $direktur->name ?? '.........................' }}</u></strong> <br>
                NIDN. {{ $direktur->nidn ?? '-' }}
            </td>
        </tr>
    </table>

</body>

</html>
