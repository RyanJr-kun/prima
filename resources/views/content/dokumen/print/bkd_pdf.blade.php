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
    @foreach ($laporan as $data)
        @php
            $dosen = $data['dosen'];
            $groups = $data['matkul_grouped'];
            $rowspanDosen = $groups->count() + 1; // +1 untuk baris Total
        @endphp

        {{-- LOOP MATKUL (GROUP) --}}
        @foreach ($groups as $namaMatkul => $items)
            <tr>
                {{-- Kolom Dosen (Muncul sekali pakai Rowspan) --}}
                @if ($loop->first)
                    <td rowspan="{{ $rowspanDosen }}">{{ $dosen->name }}</td>
                @endif

                {{-- Kolom Matkul --}}
                <td>{{ $namaMatkul }}</td>

                {{-- Kolom Shift (Loop item untuk cek shift) --}}
                {{-- Logic: Anda bisa ambil shift dari string kelas atau relasi --}}
                <td>Reguler</td>

                {{-- Kolom Kelas (Digabung pakai Comma) --}}
                {{-- Contoh: TRPL 1A, TRPL 1B --}}
                <td>
                    @foreach ($items as $item)
                        {{ explode(' - Kelas ', $item->activity_name)[1] }}@if (!$loop->last)
                            ,
                        @endif
                    @endforeach
                </td>

                {{-- SKS (Ambil dari item pertama) --}}
                <td>{{ $items->first()->sks_load }}</td>

                {{-- Jumlah Kelas (Count item di group) --}}
                <td>{{ $items->count() }}</td>

                {{-- Total SKS (SKS x Jumlah Kelas) --}}
                <td>{{ $items->first()->sks_load * $items->count() }}</td>

                {{-- Pertemuan (Ambil rata-rata atau salah satu) --}}
                <td>{{ $items->first()->realisasi_pertemuan }}</td>

                {{-- Ujian --}}
                <td>{{ $items->first()->jenis_ujian }}</td>
            </tr>
        @endforeach

        {{-- BARIS TOTAL PER DOSEN --}}
        <tr>
            <td colspan="7">Total BKD</td>
            <td>{{ $groups->flatten()->sum('sks_load') }}</td>
            <td colspan="2"></td>
        </tr>
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
