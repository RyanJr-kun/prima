<!DOCTYPE html>
<html>

<head>
    <title>Laporan BKD - {{ $doc->prodi->name }}</title>
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

        /* Table Styles */
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
            vertical-align: middle;
        }

        .data-table th {
            text-align: center;
            font-weight: bold;
            height: 30px;
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

        .bg-gray {
            background-color: #f2f2f2;
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

    <div class="text-center" style="margin-bottom: 20px;">
        <h3 style="margin:0; text-transform: uppercase; text-decoration: underline;">
            LAPORAN BEBAN KERJA DOSEN (BKD)
        </h3>
        <span>Tahun Akademik {{ $doc->academicPeriod->name }} - Program Studi {{ $doc->prodi->name }}</span>
    </div>

    {{-- TABEL UTAMA --}}
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="20%">Dosen</th>
                <th width="25%">Mata Kuliah</th>
                <th width="5%">Kelas</th>
                <th width="10%">Smt/Kelas</th>
                <th width="4%">SKS</th>
                <th width="5%">Jml<br>Kelas</th>
                <th width="5%">Jml<br>SKS</th>
                <th width="5%">Pert.</th>
                <th width="10%">Ujian</th>
                <th width="5%">SKS<br>Real</th>
            </tr>
        </thead>
        @foreach ($reportData as $idx => $dosen)
            <tbody>
                {{-- HAPUS LOGIC $rowspan --}}

                @if (count($dosen['matkuls']) > 0)
                    @foreach ($dosen['matkuls'] as $key => $mk)
                        <tr>
                            {{-- KOLOM NO: Mainkan Border Atas/Bawah --}}
                            {{-- Jika baris pertama ($key==0), border atas ada. Baris selanjutnya border atas hilang --}}
                            <td class="text-center"
                                style="border-bottom: none; {{ $key > 0 ? 'border-top: none;' : '' }}">
                                {{ $key === 0 ? $idx + 1 : '' }}
                            </td>

                            {{-- KOLOM DOSEN: Mainkan Border Atas/Bawah --}}
                            <td style="border-bottom: none; {{ $key > 0 ? 'border-top: none;' : '' }}">
                                @if ($key === 0)
                                    <b>{{ $dosen['user']->name }}</b><br>
                                    NIDN: {{ $dosen['user']->nidn ?? '-' }}
                                @endif
                            </td>

                            {{-- KOLOM LAINNYA (Normal) --}}
                            <td>{{ $mk['nama_matkul'] }}</td>
                            <td class="text-center">{{ $mk['kelas_type'] }}</td>
                            <td>{{ $mk['daftar_kelas'] }}</td>
                            <td class="text-center">{{ $mk['sks_per_mk'] }}</td>
                            <td class="text-center">{{ $mk['jml_kelas'] }}</td>
                            <td class="text-center">{{ $mk['jml_sks_total'] }}</td>
                            <td class="text-center">{{ $mk['pertemuan'] }}</td>
                            <td class="text-center">{{ $mk['ujian'] }}</td>
                            <td class="text-center font-bold">{{ $mk['sks_real'] }}</td>
                        </tr>
                    @endforeach

                    {{-- BARIS TOTAL --}}
                    {{-- Kita harus "menutup" border bawah kolom No & Dosen secara manual disini --}}
                    <tr>
                        {{-- Tutup border kolom 1 dan 2 --}}
                        <td style="border-top: none; border-bottom: 1px solid #000;"></td>
                        <td style="border-top: none; border-bottom: 1px solid #000;"></td>

                        <td colspan="6" class="text-right font-bold">Total BKD</td>
                        <td class="text-center font-bold">{{ $dosen['total_sks_bkd'] }}</td>
                        <td colspan="1"></td>
                        <td class="text-center font-bold">{{ $dosen['total_sks_real'] }}</td>
                    </tr>
                @endif
            </tbody>
        @endforeach
    </table>

    {{-- TANDA TANGAN (Sama persis dengan file user) --}}
    @php
        $wadir1 = \App\Models\User::role('wadir1')->first();
        $wadir2 = \App\Models\User::role('wadir2')->first();
        $direktur = \App\Models\User::role('direktur')->first();
        // Cari Kaprodi
        $kaprodi = \App\Models\User::find($doc->prodi->kaprodi_id);
    @endphp

    <table width="100%" style="margin-top: 30px; page-break-inside: avoid;">
        <tr>
            {{-- Wadir 1 --}}
            <td width="33%" class="text-center" style="vertical-align: top;">
                Mengetahui,<br>Wakil Direktur I<br><br>
                @if ($wadir1 && !empty($wadir1->signature_path))
                    <img src="{{ public_path('storage/' . $wadir1->signature_path) }}"
                        style="height: 90px; width: auto; margin-top: -15px; margin-bottom: -10px;">
                @else
                    <br><br><br>
                @endif
                <br><strong><u>{{ $wadir1->name ?? '.........................' }}</u></strong><br>
                NIDN. {{ $wadir1->nidn ?? '-' }}
            </td>

            {{-- Wadir 2 --}}
            <td width="33%" class="text-center" style="vertical-align: top;">
                Mengetahui,<br>Wakil Direktur II<br><br>
                @if ($wadir2 && !empty($wadir2->signature_path))
                    <img
                        src="{{ public_path('storage/' . $wadir2->signature_path) }}"style="height: 90px; width: auto; margin-top: -15px; margin-bottom: -10px;">
                @else
                    <br><br><br>
                @endif
                <br><strong><u>{{ $wadir2->name ?? '.........................' }}</u></strong><br>
                NIDN. {{ $wadir2->nidn ?? '-' }}
            </td>

            {{-- Kaprodi --}}
            <td width="33%" class="text-center" style="vertical-align: top;">
                Surakarta, {{ now()->translatedFormat('d F Y') }}<br>Ketua Program Studi<br><br>
                @if ($kaprodi && !empty($kaprodi->signature_path))
                    <img src="{{ public_path('storage/' . $kaprodi->signature_path) }}"
                        style="height: 90px; width: auto; margin-top: -15px; margin-bottom: -10px;">
                @else
                    <br><br><br>
                @endif
                <br><strong><u>{{ $kaprodi->name ?? '.........................' }}</u></strong><br>
                NIDN. {{ $kaprodi->nidn ?? '-' }}
            </td>
        </tr>
    </table>

    {{-- Direktur --}}
    <table width="100%" style="margin-top: 10px; page-break-inside: avoid;">
        <tr>
            <td class="text-center">
                Menyetujui,<br>Direktur<br><br>
                @if ($direktur && !empty($direktur->signature_path))
                    <img src="{{ public_path('storage/' . $direktur->signature_path) }}"
                        style="height: 90px; width: auto; margin-top: -15px; margin-bottom: -10px;">
                @else
                    <br><br><br>
                @endif
                <br><strong><u>{{ $direktur->name ?? '.........................' }}</u></strong><br>
                NIDN. {{ $direktur->nidn ?? '-' }}
            </td>
        </tr>
    </table>

</body>

</html>
